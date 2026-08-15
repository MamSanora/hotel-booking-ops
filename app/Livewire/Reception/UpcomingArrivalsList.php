<?php

namespace App\Livewire\Reception;

use App\Models\Booking;
use Livewire\Component;

class UpcomingArrivalsList extends Component
{
    public function refreshData()
    {
        $count = Booking::upcomingArrivals()->count();
        $this->dispatch('update-arrivals-count', count: $count);
    }

    public function render()
    {
        $upcomingArrivals = Booking::with(['guest.phones', 'room.roomType', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->upcomingArrivals()
            ->orderBy('check_in_date')
            ->get();

        return view('livewire.reception.upcoming-arrivals-list', [
            'upcomingArrivals' => $upcomingArrivals
        ]);
    }
}
