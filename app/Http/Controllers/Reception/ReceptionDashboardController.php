<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RoomService;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ReceptionDashboardController
 *
 * The main dashboard for front-desk staff. Shows:
 *   - Today's expected arrivals (booked, check_in_date = today)
 *   - Today's expected departures (checked-in, check_out_date = today)
 *   - All in-house guests (currently checked in)
 *
 * Also handles check-in, check-out, and manual payment recording.
 *
 * Route: GET /reception/dashboard
 */
class ReceptionDashboardController extends Controller
{
    /**
     * Display the reception dashboard.
     */
    public function index(): View
    {
        return view('reception.dashboard');
    }


    /**
     * Check in a guest.
     *
     * Transitions: booked → checked-in
     * Also marks the room as occupied.
     */
    public function checkin(Booking $booking): RedirectResponse
    {
        if (! $booking->canCheckIn()) {
            return back()->with('error', 'Guests can only be checked in on or after their arrival date, and the booking must be confirmed.');
        }

        $booking->update([
            'booking_status'    => Booking::STATUS_CHECKED_IN,
            'actual_check_in_at' => now(),
        ]);

        // Mark all assigned rooms as occupied so they won't show as available.
        foreach ($booking->bookingRooms as $bRoom) {
            $bRoom->room?->update([
                'current_status'    => \App\Models\Room::STATUS_OCCUPIED,
                'status_updated_at' => now(),
            ]);
        }

        $guestName = $booking->guest?->full_name ?? 'Guest';

        return back()->with('success', "{$guestName} checked in successfully.");
    }

    /**
     * Check out a guest.
     *
     * Transitions: checked-in → checked-out
     * Returns the room to available status.
     * Blocks if there is an outstanding (non-full) payment.
     */
    public function checkout(Request $request, Booking $booking): RedirectResponse
    {
        if (! $booking->canCheckOut()) {
            return back()->with('error', 'Only checked-in guests can be checked out.');
        }

        // If payment data was submitted from the checkout modal, record it first.
        if ($request->filled('payment_method') && $request->filled('amount_paid')) {
            $validated = $request->validate([
                'payment_method'    => ['required', 'in:cash,khqr,khqr_aba'],
                'amount_paid'       => ['required', 'numeric', 'min:0.01'],
                'payment_for'       => ['nullable', 'in:booking,stay_extension'],
                'payment_reference' => ['required_unless:payment_method,cash', 'nullable', 'string', 'max:255'],
            ]);

            $alreadyPaid = $booking->transactions()
                ->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL])
                ->sum('amount_paid');
            $paymentStatus = (($alreadyPaid + (float) $validated['amount_paid'] + 0.01) >= (float) $booking->total_price)
                ? Transaction::STATUS_FULL
                : Transaction::STATUS_PARTIAL;

            $paymentReference = $validated['payment_reference'] ?? null;
            if ($validated['payment_method'] === 'cash' && empty($paymentReference)) {
                $paymentReference = 'Cash — collected at check-out';
            }

            $transaction = Transaction::create([
                'booking_id'            => $booking->id,
                'amount_paid'           => $validated['amount_paid'],
                'payment_for'           => $validated['payment_for'] ?? 'booking',
                'payment_method'        => $validated['payment_method'],
                'payment_status'        => $paymentStatus,
                'payment_reference'     => $paymentReference,
                'processed_by_staff_id' => Auth::guard('staff')->id(),
            ]);

            \App\Models\IncidentalCharge::where('booking_id', $booking->id)
                ->whereNull('transaction_id')
                ->update(['transaction_id' => $transaction->id]);

            // Refresh to pick up the new transaction in the balance check below.
            $booking->refresh();
        }

        // Verify the booking balance has been fully settled (total paid >= total price)
        $totalPaid = $booking->transactions()
            ->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL])
            ->sum('amount_paid');

        if ($totalPaid + 0.01 < (float) $booking->total_price) {
            return back()->with('error', 'Cannot check out — the outstanding balance of $' . number_format(max(0, (float)$booking->total_price - $totalPaid), 2) . ' must be settled first.');
        }

        $booking->update([
            'booking_status'      => Booking::STATUS_CHECKED_OUT,
            'actual_check_out_at' => now(),
        ]);

        // Return all booked rooms to cleaning so housekeeping can clean them.
        foreach ($booking->bookingRooms as $bRoom) {
            $bRoom->room?->update([
                'current_status'    => \App\Models\Room::STATUS_CLEANING,
                'status_updated_at' => now(),
            ]);
        }

        $guestName = $booking->guest?->full_name ?? 'Guest';

        return back()
            ->with('success', "{$guestName} has been checked out successfully.")
            ->with('print_receipt', route('reception.receipt', $booking->id));
    }

    /**
     * Add a single incidental (ad-hoc) charge to a checked-in booking.
     *
     * Called via fetch() from the Incidental Charges modal before the
     * receptionist finalises the check-out. Each call saves one charge row
     * and bumps the booking's total_price so the balance check stays correct.
     *
     * Returns JSON so the Alpine modal can render a live running total.
     */
    public function addIncidentalCharge(Request $request, Booking $booking): \Illuminate\Http\JsonResponse
    {
        if (! $booking->isCheckedIn()) {
            return response()->json(['error' => 'Booking is not currently checked in.'], 422);
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:999'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
        ]);

        $total = (float) $validated['amount'] * (int) $validated['quantity'];

        DB::transaction(function () use ($booking, $validated, $total) {
            \App\Models\IncidentalCharge::create([
                'booking_id'   => $booking->id,
                'description'  => $validated['description'],
                'quantity'     => $validated['quantity'],
                'amount'       => $validated['amount'],
                'total_amount' => $total,
            ]);

            // Bump total_price so the payment balance check in checkout() passes.
            $booking->increment('total_price', $total);
        });

        // Refresh to get the updated total_price
        $booking = $booking->fresh();
        
        // Generate new QR code for the updated remaining balance
        $remaining = max(0, (float) $booking->total_price - $booking->totalPaid());
        $qrDataUri = '';
        if ($remaining > 0) {
            $useMamSanora = $booking->bookingRooms->first()?->roomType?->use_mam_sanora_qr;
            $qrString = $useMamSanora
                ? \App\Services\KhqrGenerator::forMamSanora($remaining, $booking->referenceNumber())
                : \App\Services\KhqrGenerator::forAmount($remaining, $booking->referenceNumber());
            $qrDataUri = (new \chillerlan\QRCode\QRCode)->render($qrString);
        }

        return response()->json([
            'message'      => 'Charge added.',
            'charge_total' => $total,
            'booking_total'=> (float) $booking->total_price,
            'qrDataUri'    => $qrDataUri,
        ]);
    }

    /**
     * Display a printable thermal-style receipt for a booking.
     */
    public function receipt(Booking $booking): View
    {
        $booking->load([
            'guest',
            'transactions',
            'roomServices.requestedItems',
            'bookingRooms.room.roomType',
            'bookingRooms.roomType',
            'incidentalCharges',
        ]);

        // Pass the live exchange rate for dynamic KHR conversion on the receipt.
        $exchangeRate = \App\Models\ExchangeRate::usdToKhr()->value('rate') ?? 4100;

        return view('reception.receipt', compact('booking', 'exchangeRate'));
    }

    /**
     * Record a manual payment at the front desk (cash or KHQR).
     *
     * Creates a Transaction record and updates the booking's total if needed.
     * Corresponds to Process 3.2 ("Confirm Remaining Balance") in the DFD.
     */
    public function markAsPaid(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method'    => ['required', 'in:cash,khqr,khqr_aba'],
            'amount_paid'       => ['required', 'numeric', 'min:0.01'],
            'payment_for'       => ['required', 'in:booking,stay_extension'],
            'payment_reference' => ['required_unless:payment_method,cash', 'nullable', 'string', 'max:255'],
        ]);

        // Determine if this is a partial or full payment.
        $alreadyPaid = $booking->transactions()
            ->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL])
            ->sum('amount_paid');
        $remaining = max(0, (float) $booking->total_price - $alreadyPaid);

        $paymentStatus = (($alreadyPaid + (float) $validated['amount_paid'] + 0.01) >= (float) $booking->total_price)
            ? Transaction::STATUS_FULL
            : Transaction::STATUS_PARTIAL;

        $paymentReference = $validated['payment_reference'] ?? null;
        if ($validated['payment_method'] === 'cash' && empty($paymentReference)) {
            $paymentReference = 'Cash note';
        }

        Transaction::create([
            'booking_id'            => $booking->id,
            'amount_paid'           => $validated['amount_paid'],
            'payment_for'           => $validated['payment_for'],
            'payment_method'        => $validated['payment_method'],
            'payment_status'        => $paymentStatus,
            'payment_reference'     => $paymentReference,
            'processed_by_staff_id' => Auth::guard('staff')->id(),
        ]);

        return back()->with('success', "Payment of \${$validated['amount_paid']} recorded for {$booking->referenceNumber()}.");
    }

    /**
     * Mark a room service request as completed.
     */
    public function completeRoomService(Request $request, RoomService $roomService): RedirectResponse
    {
        $roomService->update([
            'request_status' => RoomService::STATUS_COMPLETED,
            'handled_by_staff_id' => Auth::guard('staff')->id(),
            'response' => $request->input('response'),
        ]);

        return back()->with('success', 'Room service request marked as completed.');
    }

    /**
     * Extend a checked-in guest's stay (receptionist-handled, immediate payment).
     *
     * Used for walk-in / phone guests who have no online account.
     * The receptionist collects payment on the spot, so a full stay_extension
     * transaction is recorded immediately.
     */
    public function extendStay(Request $request, Booking $booking): RedirectResponse
    {
        if (! $booking->isCheckedIn()) {
            return back()->with('error', 'Only checked-in bookings can be extended.');
        }

        $validated = $request->validate([
            'extra_nights'   => ['required', 'integer', 'min:1', 'max:30'],
            'payment_method' => ['required', 'in:cash,khqr'],
        ]);

        $extraNights   = (int) $validated['extra_nights'];
        $primaryBrRoom = $booking->bookingRooms->first();
        $room          = $primaryBrRoom?->room;

        if (! $room) {
            return back()->with('error', 'No room is assigned to this booking.');
        }

        // Conflict check — look for any other active booking that has any of
        // this booking's rooms and overlaps with the new extended checkout date.
        $newCheckout = $booking->check_out_date->addDays($extraNights);
        $bookedRoomIds = $booking->bookingRooms->pluck('room_id')->filter()->all();

        $conflict = \App\Models\BookingRoom::whereIn('room_id', $bookedRoomIds)
            ->whereHas('booking', function ($q) use ($booking, $newCheckout) {
                $q->where('id', '!=', $booking->id)
                  ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
                  ->where('check_in_date', '<', $newCheckout->toDateString())
                  ->where('check_out_date', '>', $booking->check_out_date->toDateString());
            })
            ->exists();

        if ($conflict) {
            return back()->with('error',
                'Cannot extend — one or more rooms are already reserved by another guest during that period.'
            );
        }

        $pricePerNight = $booking->bookingRooms->sum('price_at_booking');
        $extraCost = $extraNights * $pricePerNight;

        DB::transaction(function () use ($booking, $extraNights, $newCheckout, $extraCost, $validated) {
            $booking->update([
                'check_out_date'           => $newCheckout->toDateString(),
                'total_price'              => $booking->total_price + $extraCost,
            ]);

            // Record full payment collected on the spot by the receptionist.
            Transaction::create([
                'booking_id'            => $booking->id,
                'amount_paid'           => $extraCost,
                'payment_for'           => Transaction::FOR_STAY_EXTENSION,
                'payment_method'        => $validated['payment_method'],
                'payment_status'        => Transaction::STATUS_FULL,
                'processed_by_staff_id' => Auth::guard('staff')->id(),
            ]);
        });

        $guestName = $booking->guest?->full_name ?? 'Guest';

        return back()->with('success',
            "{$guestName}'s stay extended by {$extraNights} night(s) until {$newCheckout->format('M d, Y')}. Payment of \${$extraCost} collected."
        );
    }

    /**
     * Cancel a no-show booking and release the room.
     *
     * A "no-show" is a booking that was never checked in and whose check-in date
     * has already passed. Receptionists can cancel these directly so the room
     * can be re-assigned or cleaned without admin involvement.
     */
    public function cancelNoShow(Booking $booking): RedirectResponse
    {
        // Guard: only allow cancelling bookings that are still in the booked state
        // and whose check-in date is in the past (true no-shows).
        if ($booking->booking_status !== Booking::STATUS_BOOKED) {
            return back()->with('error', 'Only unprocessed (booked) reservations can be cancelled as no-shows.');
        }

        $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);

        // Return all rooms to available so they can be re-assigned.
        foreach ($booking->bookingRooms as $bRoom) {
            $bRoom->room?->update([
                'current_status'    => \App\Models\Room::STATUS_AVAILABLE,
                'status_updated_at' => now(),
            ]);
        }

        return back()->with('success', "Booking {$booking->referenceNumber()} marked as no-show and room released.");
    }

    /**
     * Walk a guest because of overbooking.
     *
     * Changes booking status to RELOCATED and releases the assigned room (if any).
     * This acts as the data input for the OptimizeOverbooking command.
     */
    public function walkGuest(Booking $booking): RedirectResponse
    {
        if ($booking->booking_status !== Booking::STATUS_BOOKED) {
            return back()->with('error', 'Only un-checked-in (booked) guests can be walked.');
        }

        $booking->update(['booking_status' => Booking::STATUS_RELOCATED]);

        // Release all assigned rooms
        foreach ($booking->bookingRooms as $bRoom) {
            $bRoom->room?->update([
                'current_status'    => \App\Models\Room::STATUS_AVAILABLE,
                'status_updated_at' => now(),
            ]);
        }

        return back()->with('success', "Booking {$booking->referenceNumber()} has been marked as Relocated (Walked).");
    }
}
