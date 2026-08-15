<?php

namespace App\Livewire\Guest;

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class BookingList extends Component
{
    public $flashMessage = '';
    public $flashType = 'success';

    #[On('cancel-booking')]
    public function cancelBooking($bookingId)
    {
        $guestId = Auth::user()->guest_id;
        $booking = Booking::where('id', $bookingId)->where('guest_id', $guestId)->firstOrFail();

        if (! $booking->canCancel()) {
            $this->flashMessage = 'This booking cannot be cancelled at this stage.';
            $this->flashType = 'error';
            return;
        }

        DB::transaction(function () use ($booking) {
            $booking->update([
                'booking_status' => Booking::STATUS_CANCELLED,
            ]);
        });

        $message = "Booking {$booking->referenceNumber()} has been cancelled.";
        
        $hasPaid = $booking->transactions()->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL])->exists();
        if ($hasPaid) {
            $message .= " Your payment is non-refundable per our cancellation policy.";
        }
        
        $this->flashMessage = $message;
        $this->flashType = 'success';
    }

    public function render()
    {
        $guestId = Auth::user()->guest_id;

        // Stats Counters
        $upcomingStaysCount = Booking::where('guest_id', $guestId)
            ->whereIn('booking_status', [Booking::STATUS_PENDING, Booking::STATUS_BOOKED])
            ->count();

        $currentStaysCount = Booking::where('guest_id', $guestId)
            ->where('booking_status', Booking::STATUS_CHECKED_IN)
            ->count();

        $pastStaysCount = Booking::where('guest_id', $guestId)
            ->where('booking_status', Booking::STATUS_CHECKED_OUT)
            ->count();
            
        // Calculate Total Nights Stayed from checked-in and checked-out bookings
        $totalNightsCount = 0;
        $validStaysForNights = Booking::where('guest_id', $guestId)
            ->whereIn('booking_status', [Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])
            ->get();
            
        foreach ($validStaysForNights as $stay) {
            $totalNightsCount += $stay->nightCount();
        }

        // Upcoming bookings
        $upcomingBookings = Booking::with(['room.roomType', 'bookingRooms.roomType'])
            ->where('guest_id', $guestId)
            ->whereNotIn('booking_status', [
                Booking::STATUS_CHECKED_OUT,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_NO_SHOW,
                Booking::STATUS_RELOCATED,
                Booking::STATUS_SNATCHED,
                Booking::STATUS_ABANDONED,
            ])
            ->whereDate('check_out_date', '>=', today())
            ->orderBy('check_in_date')
            ->get();

        // Past bookings
        $pastBookings = Booking::with(['room.roomType', 'bookingRooms.roomType'])
            ->where('guest_id', $guestId)
            ->where(function ($query) {
                $query->whereIn('booking_status', [
                    Booking::STATUS_CHECKED_OUT,
                    Booking::STATUS_CANCELLED,
                    Booking::STATUS_NO_SHOW,
                    Booking::STATUS_RELOCATED,
                    Booking::STATUS_SNATCHED,
                    Booking::STATUS_ABANDONED,
                ])->orWhereDate('check_out_date', '<', today());
            })
            ->orderByDesc('check_out_date')
            ->limit(10)
            ->get();

        return view('livewire.guest.booking-list', compact(
            'upcomingBookings', 
            'pastBookings',
            'upcomingStaysCount',
            'currentStaysCount',
            'pastStaysCount',
            'totalNightsCount'
        ));
    }
}
