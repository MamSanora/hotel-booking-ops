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
        // Guests arriving today — booking confirmed, not yet checked in.
        $todayArrivals = Booking::with(['guest', 'room'])
            ->arrivingToday()
            ->orderBy('check_in_date')
            ->get();

        // Guests departing today — currently checked in.
        $todayDepartures = Booking::with(['guest', 'room'])
            ->departingToday()
            ->orderBy('check_out_date')
            ->get();

        // All guests currently in the hotel.
        $inHouseGuests = Booking::with(['guest', 'room'])
            ->checkedIn()
            ->orderBy('check_out_date')
            ->get();

        // Pending room service requests
        $pendingRoomServices = RoomService::with(['booking.room', 'booking.guest', 'requestedItems.catalog'])
            ->pending()
            ->oldest()
            ->get();

        return view('reception.dashboard', compact(
            'todayArrivals',
            'todayDepartures',
            'inHouseGuests',
            'pendingRoomServices',
        ));
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
            return back()->with('error', 'Only confirmed (booked) bookings can be checked in.');
        }

        $booking->update(['booking_status' => Booking::STATUS_CHECKED_IN]);

        // Mark the room as occupied so it won't show as available.
        $booking->room?->update(['current_status' => 'occupied']);

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

        // Verify the booking has at least one fully-paid transaction.
        $hasFullPayment = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_FULL)
            ->exists();

        if (! $hasFullPayment) {
            return back()->with('error', 'Cannot check out — the outstanding balance must be settled first.');
        }

        $booking->update(['booking_status' => Booking::STATUS_CHECKED_OUT]);

        // Return the room to available so it can be booked again.
        $booking->room?->update(['current_status' => 'available']);

        $guestName = $booking->guest?->full_name ?? 'Guest';

        return back()->with('success', "{$guestName} has been checked out successfully.");
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
            'payment_method' => ['required', 'in:cash,khqr'],
            'amount_paid'    => ['required', 'numeric', 'min:0.01'],
            'payment_for'    => ['required', 'in:booking,stay_extension'],
        ]);

        // Determine if this is a partial or full payment.
        $remaining = (float) $booking->total_price
            - $booking->transactions()->where('payment_status', Transaction::STATUS_FULL)->sum('amount_paid');

        $paymentStatus = ((float) $validated['amount_paid'] >= $remaining)
            ? Transaction::STATUS_FULL
            : Transaction::STATUS_HALF;

        Transaction::create([
            'booking_id'     => $booking->id,
            'amount_paid'    => $validated['amount_paid'],
            'payment_for'    => $validated['payment_for'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $paymentStatus,
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

        $extraCost = $extraNights * (float) $room->price_per_night;

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
}
