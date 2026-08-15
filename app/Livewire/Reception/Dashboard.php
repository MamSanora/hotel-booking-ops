<?php

namespace App\Livewire\Reception;

use App\Models\Booking;
use App\Models\RoomService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $upcomingArrivals = Booking::with(['guest.phones', 'room', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->upcomingArrivals()
            ->orderBy('check_in_date')
            ->get();

        $noShows = Booking::with(['guest.phones', 'room', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->where('booking_status', Booking::STATUS_BOOKED)
            ->whereDate('check_in_date', '<', today())
            ->whereDate('check_in_date', '>=', now()->startOfMonth())
            ->orderBy('check_in_date')
            ->get();

        $arrivalsToday = Booking::with(['guest.phones', 'room.roomType', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->arrivingToday()
            ->orderBy('check_in_date')
            ->get();

        $todayDepartures = Booking::with(['guest.phones', 'room.roomType', 'transactions', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->departingToday()
            ->orderBy('check_out_date')
            ->get();

        $inHouseGuests = Booking::with(['guest.phones', 'room.roomType', 'transactions', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->checkedIn()
            ->orderBy('check_out_date')
            ->get();

        $extensionLimits = [];
        foreach ($inHouseGuests as $booking) {
            if (! $booking->room_id) {
                $extensionLimits[$booking->id] = ['max_nights' => 30, 'next_booking' => null];
                continue;
            }

            $nextConflict = Booking::where('room_id', $booking->room_id)
                ->where('id', '!=', $booking->id)
                ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
                ->where('check_in_date', '>=', $booking->check_out_date->toDateString())
                ->orderBy('check_in_date')
                ->first();

            if ($nextConflict) {
                $maxNights = (int) $booking->check_out_date->diffInDays($nextConflict->check_in_date);
            } else {
                $maxNights = 30;
            }

            $extensionLimits[$booking->id] = [
                'max_nights'   => $maxNights,
                'next_booking' => $nextConflict,
            ];
        }

        $pendingRoomServices = RoomService::with(['booking.room', 'booking.guest', 'requestedItems.catalog'])
            ->pending()
            ->latest()
            ->get();

        $recentHistory = Booking::with(['guest.phones', 'room', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->recentHistory()
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.reception.dashboard', compact(
            'upcomingArrivals',
            'arrivalsToday',
            'todayDepartures',
            'inHouseGuests',
            'extensionLimits',
            'pendingRoomServices',
            'recentHistory',
            'noShows',
        ));
    }
}
