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
        $pendingRoomServices = RoomService::with(['room', 'booking.guest', 'requestedItems.catalog'])
            ->pending()
            ->latest()
            ->get();

        return view('livewire.reception.housekeeping-requests-list', [
            'pendingRoomServices' => $pendingRoomServices
        ]);
    }

    public function completeRoomService($roomServiceId, $response = null)
    {
        $roomService = RoomService::findOrFail($roomServiceId);
        
        $roomService->update([
            'request_status' => RoomService::STATUS_COMPLETED,
            'handled_by_staff_id' => \Illuminate\Support\Facades\Auth::guard('staff')->id(),
            'response' => $response,
        ]);

        session()->flash('success', 'Room service request marked as completed.');
        $this->dispatch('room-service-completed');
    }
}
