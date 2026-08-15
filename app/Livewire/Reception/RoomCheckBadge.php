<?php

namespace App\Livewire\Reception;

use App\Models\Room;
use Livewire\Component;

class RoomCheckBadge extends Component
{
    public function render()
    {
        $pendingClean = Room::whereIn('current_status', ['cleaning', 'maintenance'])->count();
        return view('livewire.reception.room-check-badge', compact('pendingClean'));
    }
}
