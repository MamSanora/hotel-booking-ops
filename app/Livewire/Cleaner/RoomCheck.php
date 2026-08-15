<?php

namespace App\Livewire\Cleaner;

use App\Models\Room;
use Livewire\Component;

class RoomCheck extends Component
{
    public $flashMessage = '';
    public $flashType = 'success';

    public function markAvailable(Room $room)
    {
        if ($room->current_status === Room::STATUS_OCCUPIED) {
            $this->flashMessage = "Room {$room->room_number} is currently occupied and cannot be marked as available.";
            $this->flashType = 'error';
            return;
        }

        if ($room->current_status === Room::STATUS_AVAILABLE) {
            $this->flashMessage = "Room {$room->room_number} is already available.";
            $this->flashType = 'error';
            return;
        }

        $previousStatus = $room->current_status;
        $room->update([
            'current_status'   => Room::STATUS_AVAILABLE,
            'status_updated_at' => now(),
        ]);

        $this->flashMessage = "Room {$room->room_number} marked as available (was: {$previousStatus}).";
        $this->flashType = 'success';
    }

    public function render()
    {
        $cleaningRooms    = Room::with('roomType')->cleaning()->orderBy('room_number')->get();
        $maintenanceRooms = Room::with('roomType')->maintenance()->orderBy('room_number')->get();
        $availableRooms   = Room::with('roomType')->available()->orderBy('room_number')->get();

        return view('livewire.cleaner.room-check', compact(
            'cleaningRooms',
            'maintenanceRooms',
            'availableRooms'
        ));
    }
}
