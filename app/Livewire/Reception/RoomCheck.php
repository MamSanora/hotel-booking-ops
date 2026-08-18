<?php

namespace App\Livewire\Reception;

use App\Models\Room;
use App\Models\RoomType;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\Component;

class RoomCheck extends Component
{
    #[Url]
    public $status = '';

    #[Url]
    public $type = '';

    #[Url]
    public $sort = '';

    public $flashMessage = '';
    public $flashType = 'success';

    #[On('mark-available')]
    public function markAvailable($roomId)
    {
        $room = Room::findOrFail($roomId);
        
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
        $room->update(['current_status' => Room::STATUS_AVAILABLE, 'status_updated_at' => now()]);

        $this->flashMessage = "Room {$room->room_number} marked as available (was: {$previousStatus}).";
        $this->flashType = 'success';
    }

    #[On('mark-maintenance')]
    public function markMaintenance($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        if ($room->current_status === Room::STATUS_OCCUPIED) {
            $this->flashMessage = "Room {$room->room_number} is currently occupied and cannot be placed under maintenance.";
            $this->flashType = 'error';
            return;
        }

        if ($room->current_status === Room::STATUS_MAINTENANCE) {
            $this->flashMessage = "Room {$room->room_number} is already under maintenance.";
            $this->flashType = 'error';
            return;
        }

        $previousStatus = $room->current_status;
        $room->update(['current_status' => Room::STATUS_MAINTENANCE, 'status_updated_at' => now()]);

        $this->flashMessage = "Room {$room->room_number} marked for maintenance (was: {$previousStatus}).";
        $this->flashType = 'success';
    }

    #[On('mark-cleaning')]
    public function markCleaning($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        if ($room->current_status === Room::STATUS_CLEANING) {
            $this->flashMessage = "Room {$room->room_number} is already marked for cleaning.";
            $this->flashType = 'error';
            return;
        }

        $previousStatus = $room->current_status;
        $room->update(['current_status' => Room::STATUS_CLEANING, 'status_updated_at' => now()]);

        $this->flashMessage = "Room {$room->room_number} marked as vacated and requires cleaning.";
        $this->flashType = 'success';
    }

    public function render()
    {
        $cleaningRooms = Room::with('roomType')->cleaning()->orderBy('room_number')->get();
        $maintenanceRooms = Room::with('roomType')->maintenance()->orderBy('room_number')->get();
        $availableRooms = Room::with('roomType')->available()->orderBy('room_number')->get();
        $occupiedRooms = Room::with(['roomType', 'activeBookings.guest'])->occupied()->orderBy('room_number')->get();

        $boardRoomsQuery = Room::with(['roomType', 'activeBookings.guest']);

        if (!empty($this->status)) {
            $boardRoomsQuery->where('current_status', $this->status);
        }

        if (!empty($this->type)) {
            $boardRoomsQuery->where('room_type_id', $this->type);
        }

        if (!empty($this->sort)) {
            switch ($this->sort) {
                case 'number_desc':
                    $boardRoomsQuery->orderBy('room_number', 'desc');
                    break;
                case 'status_asc':
                    $boardRoomsQuery->orderBy('current_status', 'asc')->orderBy('room_number', 'asc');
                    break;
                case 'status_desc':
                    $boardRoomsQuery->orderBy('current_status', 'desc')->orderBy('room_number', 'asc');
                    break;
                default:
                    $boardRoomsQuery->orderBy('room_number', 'asc');
                    break;
            }
        } else {
            $boardRoomsQuery->orderBy('room_number', 'asc');
        }

        $boardRooms = $boardRoomsQuery->get();
        
        if (!empty($this->sort)) {
             if ($this->sort === 'type_asc') {
                 $boardRooms = $boardRooms->sortBy(fn($r) => $r->roomType->display_name ?? '')->values();
             } elseif ($this->sort === 'type_desc') {
                 $boardRooms = $boardRooms->sortByDesc(fn($r) => $r->roomType->display_name ?? '')->values();
             }
        }

        $roomTypes = RoomType::orderBy('display_name')->get();

        return view('livewire.reception.room-check', compact(
            'cleaningRooms', 'maintenanceRooms', 'availableRooms', 'occupiedRooms',
            'boardRooms', 'roomTypes'
        ));
    }
}
