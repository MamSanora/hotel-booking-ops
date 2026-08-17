<?php

namespace App\Livewire\Reception;

use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class UpcomingArrivalsList extends Component
{
    use WithPagination;

    public function refreshData()
    {
        $count = Booking::upcomingArrivals()
            ->where('check_in_date', '<=', now()->addDays(7))
            ->count();
        $this->dispatch('update-arrivals-count', count: $count);
    }

    public function render()
    {
        $upcomingArrivals = Booking::with(['guest.phones', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->upcomingArrivals()
            ->where('check_in_date', '<=', now()->addDays(7))
            ->orderBy('check_in_date')
            ->paginate(15);

        return view('livewire.reception.upcoming-arrivals-list', [
            'upcomingArrivals' => $upcomingArrivals
        ]);
    }
}
