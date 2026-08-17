<?php

namespace App\Livewire\Reception;

use App\Models\Booking;
use App\Models\RoomService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $upcomingArrivalsCount = Booking::upcomingArrivals()
            ->where('check_in_date', '<=', now()->addDays(7))
            ->count();

        $noShows = Booking::with(['guest.phones', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->where('booking_status', Booking::STATUS_BOOKED)
            ->whereDate('check_in_date', '<', today())
            ->whereDate('check_in_date', '>=', now()->startOfMonth())
            ->orderBy('check_in_date')
            ->get();

        $arrivalsToday = Booking::with(['guest.phones', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->arrivingToday()
            ->orderBy('check_in_date')
            ->get();

        $todayDepartures = Booking::with(['guest.phones', 'transactions', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->departingToday()
            ->orderBy('check_out_date')
            ->get();

        $inHouseGuests = Booking::with(['guest.phones', 'transactions', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->checkedIn()
            ->orderBy('check_out_date')
            ->get();

        $extensionLimits = [];
        foreach ($inHouseGuests as $booking) {
            $roomIds = $booking->bookingRooms->pluck('room_id')->filter()->all();
            if (empty($roomIds)) {
                $extensionLimits[$booking->id] = ['max_nights' => 30, 'next_booking' => null];
                continue;
            }

            // Find the earliest next booking on any of the rooms in this booking
            $nextConflict = Booking::whereHas('bookingRooms', fn($q) => $q->whereIn('room_id', $roomIds))
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

        $pendingRoomServices = RoomService::with(['booking.bookingRooms.room', 'booking.guest', 'requestedItems.catalog'])
            ->pending()
            ->latest()
            ->get();

        $recentHistory = Booking::with(['guest.phones', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->recentHistory()
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.reception.dashboard', compact(
            'upcomingArrivalsCount',
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
