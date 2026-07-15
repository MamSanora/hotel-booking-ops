<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ReceptionRelocationController
 *
 * Handles the Guest Room Relocation workflow.
 *
 * When a receptionist cannot extend a guest's stay because the current room
 * is already booked by an incoming guest, they can relocate the current guest
 * to another room of the same type.
 *
 * Flow:
 *   1. GET  /reception/relocate/{booking}        — Show available rooms
 *   2. POST /reception/relocate/{booking}/confirm — Confirm the move
 *
 * The original booking is marked 'relocated' (not checked-out — the guest
 * is still in-house). The room is freed. A new booking is created for the
 * new room and immediately put into 'checked-in' status.
 *
 * The two bookings are linked via relocated_to_booking_id so analytics
 * can trace the full guest journey.
 */
class ReceptionRelocationController extends Controller
{
    /**
     * Show the relocation page with available alternative rooms.
     *
     * Pre-filters rooms by:
     *   - Same room type as the original room (so the guest gets a similar room)
     *   - Available for the period from today until the original checkout date
     *   - Excludes the current room itself
     */
    public function show(Booking $booking): View|RedirectResponse
    {
        if (! $booking->isCheckedIn()) {
            return redirect()->route('reception.dashboard')
                ->with('error', 'Only checked-in guests can be relocated.');
        }

        $currentRoom = $booking->room;

        if (! $currentRoom) {
            return redirect()->route('reception.dashboard')
                ->with('error', 'No room is assigned to this booking.');
        }

        // Check that a relocation is actually necessary — i.e., the extension IS blocked
        $newCheckout = $booking->check_out_date->addDay(); // at minimum 1 extra night
        $conflictExists = Booking::where('room_id', $currentRoom->id)
            ->where('id', '!=', $booking->id)
            ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
            ->where('check_in_date', '<=', $booking->check_out_date->toDateString())
            ->where('check_out_date', '>', $booking->check_out_date->toDateString())
            ->exists();

        // Find the next check-in date on this room (that's the "deadline")
        $nextBooking = Booking::where('room_id', $currentRoom->id)
            ->where('id', '!=', $booking->id)
            ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
            ->where('check_in_date', '>=', $booking->check_out_date->toDateString())
            ->orderBy('check_in_date')
            ->first();

        // Available rooms: same room type, available from today to guest's intended checkout date
        // The guest keeps the same checkout date — they're just moving rooms.
        $alternativeRooms = Room::with('roomType')
            ->where('room_type_id', $currentRoom->room_type_id)
            ->where('id', '!=', $currentRoom->id)
            ->availableForDates(
                today()->toDateString(),
                $booking->check_out_date->toDateString(),
                $booking->id
            )
            ->orderBy('room_number')
            ->get();

        return view('reception.relocate', compact(
            'booking',
            'currentRoom',
            'alternativeRooms',
            'conflictExists',
            'nextBooking',
        ));
    }

    /**
     * Confirm and execute the relocation.
     *
     * Wrapped in a DB transaction to guarantee atomicity:
     *   1. Mark original booking as 'relocated'.
     *   2. Free the original room (current_status → available).
     *   3. Create a new booking for the new room (checked-in).
     *   4. Mark new room as occupied.
     *   5. Link the two bookings via relocated_to_booking_id.
     *
     * No new payment is collected here — the guest is not being charged extra.
     * They are simply changing rooms for the same period.
     */
    public function confirm(Request $request, Booking $booking): RedirectResponse
    {
        if (! $booking->isCheckedIn()) {
            return back()->with('error', 'Only checked-in guests can be relocated.');
        }

        $validated = $request->validate([
            'new_room_id' => ['required', 'integer', 'exists:rooms,id'],
        ]);

        $newRoom = Room::with('roomType')->findOrFail($validated['new_room_id']);

        // Verify the chosen room is available for this guest's remaining stay
        $isAvailable = $newRoom->isAvailableForDates(
            today()->toDateString(),
            $booking->check_out_date->toDateString(),
            $booking->id
        );

        if (! $isAvailable) {
            return back()->with('error', "Room {$newRoom->room_number} is no longer available. Please choose another.");
        }

        $staffId     = Auth::guard('staff')->id();
        $guestName   = $booking->guest?->full_name ?? 'Guest';
        $oldRoomNum  = $booking->room?->room_number ?? '?';
        $newRoomNum  = $newRoom->room_number;
        $newBooking  = null;

        DB::transaction(function () use ($booking, $newRoom, $staffId, &$newBooking) {
            // 1. Create the new booking for the new room (immediately checked-in)
            $newBooking = Booking::create([
                'guest_id'               => $booking->guest_id,
                'room_id'                => $newRoom->id,
                'handled_by_staff_id'    => $staffId,
                'check_in_date'          => today()->toDateString(),
                'check_out_date'         => $booking->check_out_date->toDateString(),
                'number_of_stay_extension' => 0,
                'total_price'            => $booking->total_price, // carry over; no extra charge
                'booking_status'         => Booking::STATUS_CHECKED_IN,
                'guest_type'             => $booking->guest_type ?? Booking::GUEST_TYPE_WALKIN,
                'special_requests'       => $booking->special_requests,
            ]);

            // 2. Mark the original booking as relocated and link it to the new one
            $booking->update([
                'booking_status'          => Booking::STATUS_RELOCATED,
                'relocated_to_booking_id' => $newBooking->id,
            ]);

            // 3. Free the original room
            $booking->room?->update(['current_status' => 'available']);

            // 4. Occupy the new room
            $newRoom->update(['current_status' => 'occupied']);
        });

        return redirect()->route('reception.dashboard')
            ->with('success',
                "{$guestName} relocated from Room {$oldRoomNum} to Room {$newRoomNum} ({$booking->referenceNumber()} → {$newBooking->referenceNumber()})."
            );
    }
}
