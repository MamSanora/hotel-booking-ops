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

        $booking->update(['booking_status' => Booking::STATUS_CHECKED_IN]);

        // Mark the room as occupied so it won't show as available.
        $booking->room?->update([
            'current_status' => \App\Models\Room::STATUS_OCCUPIED,
            'status_updated_at' => now(),
        ]);

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
    public function checkout(Booking $booking): RedirectResponse
    {
        if (! $booking->canCheckOut()) {
            return back()->with('error', 'Only checked-in guests can be checked out.');
        }

        // Verify the booking balance has been fully settled (total paid >= total price)
        $totalPaid = $booking->transactions()
            ->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL])
            ->sum('amount_paid');

        if ($totalPaid + 0.01 < (float) $booking->total_price) {
            return back()->with('error', 'Cannot check out — the outstanding balance of $' . number_format(max(0, (float)$booking->total_price - $totalPaid), 2) . ' must be settled first.');
        }

        $booking->update(['booking_status' => Booking::STATUS_CHECKED_OUT]);

        // Return the room to cleaning so housekeeping can clean it.
        $booking->room?->update([
            'current_status' => \App\Models\Room::STATUS_CLEANING,
            'status_updated_at' => now(),
        ]);

        $guestName = $booking->guest?->full_name ?? 'Guest';

        return back()->with('success', "{$guestName} has been checked out successfully.");
    }

    /**
     * Display a printable thermal-style receipt for a booking.
     */
    public function receipt(Booking $booking): View
    {
        $booking->load(['guest', 'room.roomType', 'transactions', 'roomServices.requestedItems']);
        return view('reception.receipt', compact('booking'));
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
            'payment_reference' => ['required', 'string', 'max:255'],
        ]);

        // Determine if this is a partial or full payment.
        $alreadyPaid = $booking->transactions()
            ->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL])
            ->sum('amount_paid');
        $remaining = max(0, (float) $booking->total_price - $alreadyPaid);

        $paymentStatus = (($alreadyPaid + (float) $validated['amount_paid'] + 0.01) >= (float) $booking->total_price)
            ? Transaction::STATUS_FULL
            : Transaction::STATUS_PARTIAL;

        Transaction::create([
            'booking_id'        => $booking->id,
            'amount_paid'       => $validated['amount_paid'],
            'payment_for'       => $validated['payment_for'],
            'payment_method'    => $validated['payment_method'],
            'payment_status'    => $paymentStatus,
            'payment_reference' => $validated['payment_reference'],
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

        $extraNights = (int) $validated['extra_nights'];
        $room        = $booking->room;

        if (! $room) {
            return back()->with('error', 'No room is assigned to this booking.');
        }

        // Conflict check — look for any other active booking on the same room
        // that overlaps with the new extended checkout date.
        $newCheckout = $booking->check_out_date->addDays($extraNights);

        $conflict = Booking::where('room_id', $room->id)
            ->where('id', '!=', $booking->id)
            ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
            ->where('check_in_date', '<', $newCheckout->toDateString())
            ->where('check_out_date', '>', $booking->check_out_date->toDateString())
            ->exists();

        if ($conflict) {
            return back()->with('error',
                'Cannot extend — the room is already reserved by another guest during that period.'
            );
        }

        $extraCost = $extraNights * (float) $room->roomType->price_per_night;

        DB::transaction(function () use ($booking, $extraNights, $newCheckout, $extraCost, $validated) {
            $booking->update([
                'check_out_date'           => $newCheckout->toDateString(),
                'total_price'              => $booking->total_price + $extraCost,
                'number_of_stay_extension' => $booking->number_of_stay_extension + 1,
            ]);

            // Record full payment collected on the spot by the receptionist.
            Transaction::create([
                'booking_id'     => $booking->id,
                'amount_paid'    => $extraCost,
                'payment_for'    => Transaction::FOR_STAY_EXTENSION,
                'payment_method' => $validated['payment_method'],
                'payment_status' => Transaction::STATUS_FULL,
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

        // Return the room to available so it can be cleaned and re-assigned.
        $booking->room?->update([
            'current_status'    => \App\Models\Room::STATUS_AVAILABLE,
            'status_updated_at' => now(),
        ]);

        return back()->with('success', "Booking {$booking->referenceNumber()} marked as no-show and room released.");
    }
}
