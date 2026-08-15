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
        $booking = \App\Models\Booking::with(['guest', 'room', 'transactions', 'bookingRooms'])->findOrFail($bookingId);
        
        // Ensure it's a manual booking (not a registered user's booking)
        if (!in_array($booking->booking_origin, ['walk-in', 'phone', 'other'])) {
            abort(403, 'Cannot edit a registered user booking.');
        }

        // Multi-room bookings cannot be edited via this simple form.
        if ($booking->bookingRooms->count() > 1) {
            return redirect()->route('reception.manage-bookings.index')
                ->with('error', 'Multi-room bookings cannot be edited manually. Please contact an administrator.');
        }

        return view('reception.manage_bookings.edit', compact('booking'));
    }

    public function update(Request $request, $bookingId)
    {
        $booking = \App\Models\Booking::with(['transactions', 'bookingRooms'])->findOrFail($bookingId);
        
        if (!in_array($booking->booking_origin, ['walk-in', 'phone', 'other'])) {
            abort(403, 'Cannot edit a registered user booking.');
        }

        // Multi-room bookings cannot be updated via this simple form.
        if ($booking->bookingRooms->count() > 1) {
            return redirect()->route('reception.manage-bookings.index')
                ->with('error', 'Multi-room bookings cannot be edited manually. Please contact an administrator.');
        }

        $validated = $request->validate([
            'full_name'         => ['required', 'string', 'max:255'],
            'phone_number'      => ['required', 'string', 'max:50'],
            'email'             => ['nullable', 'email', 'max:255'],
            'check_in_date'     => ['required', 'date'],
            'check_out_date'    => ['required', 'date', 'after:check_in_date'],
            'room_id'           => ['required', 'exists:rooms,id'],
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
            $room = \App\Models\Room::with('roomType')->find($validated['room_id']);
            $nights = max(1, \Carbon\Carbon::parse($validated['check_in_date'])->diffInDays(\Carbon\Carbon::parse($validated['check_out_date'])));
            $newTotal = $nights * ($room->roomType->price_per_night ?? 0);
            
            $tierMultiplier = ((float) $validated['payment_tier']) / 100;
            $requiredAmount = $newTotal * $tierMultiplier;
            
            $originalTotalPaid = $booking->transactions()->sum('amount_paid');
            $paymentDifference = $requiredAmount - $originalTotalPaid;

            $paymentReference = $validated['payment_reference'] ?? null;
            if ($validated['payment_method'] === 'cash' && empty($paymentReference)) {
                $paymentReference = 'Cash note';
            }

            // Update Booking
            $booking->update([
                'check_in_date'  => $validated['check_in_date'],
                'check_out_date' => $validated['check_out_date'],
                'room_id'        => $validated['room_id'],
                'total_price'    => $newTotal,
                'payment_tier'   => $validated['payment_tier'],
            ]);

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

        return redirect()->route('reception.manage-bookings.index')->with('success', 'Booking cancelled successfully.');
    }
}
