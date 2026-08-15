<?php

namespace App\Livewire\Reception;

use App\Models\RoomService;
use Livewire\Component;

class HousekeepingRequestsList extends Component
{
    public function refreshData()
    {
        $count = RoomService::pending()->count();
        $this->dispatch('update-housekeeping-count', count: $count);
    }

    public function render()
    {
        $pendingRoomServices = RoomService::with(['booking.room', 'booking.guest', 'requestedItems.catalog'])
            ->pending()
            ->latest()
            ->get();

        return view('livewire.reception.housekeeping-requests-list', [
            'pendingRoomServices' => $pendingRoomServices
        ]);
    }
}
