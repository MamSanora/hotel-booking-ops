<?php

namespace App\Livewire\Cleaner;

use App\Models\Room;
use Livewire\Component;

class RoomCheckBadge extends Component
{
    public function render()
    {
        $pendingRooms = Room::whereIn('current_status', ['cleaning', 'maintenance'])->count();
        return view('livewire.cleaner.room-check-badge', compact('pendingRooms'));
    }
}
