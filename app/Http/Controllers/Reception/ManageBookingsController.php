<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManageBookingsController extends Controller
{
    public function index()
    {
        return view('reception.manage_bookings.index');
    }

    public function edit($bookingId)
    {
        $booking = \App\Models\Booking::with(['guest', 'transactions', 'bookingRooms'])->findOrFail($bookingId);
        
        // Ensure it's a manual booking (not a registered user's booking)
        if (!in_array($booking->booking_origin, ['walk-in', 'phone', 'other'])) {
            abort(403, 'Cannot edit a registered user booking.');
        }



        return view('reception.manage_bookings.edit', compact('booking'));
    }

    public function update(Request $request, $bookingId)
    {
        $booking = \App\Models\Booking::with(['transactions', 'bookingRooms'])->findOrFail($bookingId);
        
        if (!in_array($booking->booking_origin, ['walk-in', 'phone', 'other'])) {
            abort(403, 'Cannot edit a registered user booking.');
        }



        $validated = $request->validate([
            'full_name'         => ['required', 'string', 'max:255'],
            'phone_number'      => ['required', 'string', 'max:50'],
            'email'             => ['nullable', 'email', 'max:255'],
            'check_in_date'     => ['required', 'date'],
            'check_out_date'    => ['required', 'date', 'after:check_in_date'],
            'room_ids'          => ['required', 'array', 'min:1'],
            'room_ids.*'        => ['integer', 'exists:rooms,id'],
            'payment_tier'      => ['required', 'in:20,50,100'],
            'amount_paid'       => ['required', 'numeric', 'min:0'],
            'payment_method'    => ['required', 'string', 'in:cash,khqr'],
            'payment_reference' => ['required_unless:payment_method,cash', 'nullable', 'string', 'max:255'],
        ]);

        \DB::transaction(function () use ($booking, $validated) {
            // Update Guest
            $booking->guest->update([
                'full_name' => $validated['full_name'],
            ]);
            $phone = $booking->guest->phones()->first();
            if ($phone) {
                $phone->update(['phone_number' => $validated['phone_number']]);
            }

            // Calculate new total and required amount based on tier
            $rooms = \App\Models\Room::with('roomType')->whereIn('id', $validated['room_ids'])->get();
            $nights = max(1, \Carbon\Carbon::parse($validated['check_in_date'])->diffInDays(\Carbon\Carbon::parse($validated['check_out_date'])));
            
            $newTotal = 0;
            foreach ($rooms as $room) {
                $newTotal += $nights * ($room->roomType->price_per_night ?? 0);
            }
            
            $tierMultiplier = ((float) $validated['payment_tier']) / 100;
            $requiredAmount = $newTotal * $tierMultiplier;
            
            $originalTotalPaid = $booking->transactions()->sum('amount_paid');
            $paymentDifference = $requiredAmount - $originalTotalPaid;

            $paymentReference = $validated['payment_reference'] ?? null;
            if ($validated['payment_method'] === 'cash' && empty($paymentReference)) {
                $paymentReference = 'Cash note';
            }

            // Update Booking (no room_id — rooms tracked in booking_room)
            $booking->update([
                'check_in_date'  => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'total_price'    => $newTotal,
                'payment_tier'   => $validated['payment_tier'],
            ]);

            // Update the booking_room rows to the newly selected rooms
            $booking->bookingRooms()->delete();
            foreach ($rooms as $room) {
                $booking->bookingRooms()->create([
                    'room_type_id'     => $room->room_type_id,
                    'room_id'          => $room->id,
                    'price_at_booking' => (float) ($room->roomType->price_per_night ?? 0),
                ]);
            }

            // Handle Financial Changes
            if ($paymentDifference > 0) {
                // Charge
                \App\Models\Transaction::create([
                    'booking_id'            => $booking->id,
                    'amount_paid'           => $paymentDifference,
                    'payment_for'           => 'modification_charge',
                    'payment_method'        => $validated['payment_method'],
                    'payment_status'        => 'full',
                    'payment_reference'     => $paymentReference,
                    'processed_by_staff_id' => Auth::guard('staff')->id(),
                ]);
            } elseif ($paymentDifference < 0) {
                // Refund
                // NOTE: Cash flow at the front desk is manual. 
                // This transaction simply logs the event so the system's ledger balances, 
                // but the receptionist must physically hand the cash back to the guest.
                
                /* 
                 * A negative transaction is required to balance the cash drawer 
                 * when a receptionist mistakenly over-reported the tier (e.g. 100% instead of 20%).
                 * This acts as an adjustment to the original mistaken cash entry.
                 */
                \App\Models\Transaction::create([
                    'booking_id'            => $booking->id,
                    'amount_paid'           => $paymentDifference, // This will be negative
                    'payment_for'           => 'tier_correction',
                    'payment_method'        => $validated['payment_method'],
                    'payment_status'        => 'refunded',
                    'payment_reference'     => $paymentReference,
                    'processed_by_staff_id' => Auth::guard('staff')->id(),
                ]);
            }
        });

        return redirect()->route('reception.manage-bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function cancel(Request $request, $bookingId)
    {
        $booking = \App\Models\Booking::findOrFail($bookingId);
        
        if (!in_array($booking->booking_origin, ['walk-in', 'phone', 'other'])) {
            abort(403, 'Cannot edit a registered user booking.');
        }

        $booking->update([
            'booking_status' => 'cancelled'
        ]);

        // Release any rooms assigned to this booking to prevent ghost "occupied" rooms
        $roomIds = \App\Models\BookingRoom::where('booking_id', $bookingId)->pluck('room_id');
        if ($roomIds->isNotEmpty()) {
            \App\Models\Room::whereIn('id', $roomIds)->update(['current_status' => 'available']);
        }

        return redirect()->route('reception.manage-bookings.index')->with('success', 'Booking cancelled successfully.');
    }
}
