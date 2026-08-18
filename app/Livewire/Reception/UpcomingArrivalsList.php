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

    public function checkIn($bookingId)
    {
        $booking = Booking::with(['guest', 'bookingRooms.room'])->findOrFail($bookingId);

        if (! $booking->canCheckIn()) {
            session()->flash('error', 'Guests can only be checked in on or after their arrival date, and the booking must be confirmed.');
            return;
        }

        $booking->update([
            'booking_status'    => Booking::STATUS_CHECKED_IN,
            'actual_check_in_at' => now(),
        ]);

        foreach ($booking->bookingRooms as $bRoom) {
            $bRoom->room?->update([
                'current_status'    => \App\Models\Room::STATUS_OCCUPIED,
                'status_updated_at' => now(),
            ]);
        }

        $guestName = $booking->guest?->full_name ?? 'Guest';
        session()->flash('success', "{$guestName} checked in successfully.");
        
        // Refresh the parent dashboard to move the guest to the "In House" tab
        $this->dispatch('guest-checked-in');
    }

    public function walkGuest($bookingId)
    {
        $booking = Booking::with('bookingRooms.room')->findOrFail($bookingId);

        if ($booking->booking_status !== Booking::STATUS_BOOKED) {
            session()->flash('error', 'Only un-checked-in (booked) guests can be walked.');
            return;
        }

        $booking->update(['booking_status' => Booking::STATUS_RELOCATED]);

        // Release all assigned rooms
        foreach ($booking->bookingRooms as $bRoom) {
            $bRoom->room?->update([
                'current_status'    => \App\Models\Room::STATUS_AVAILABLE,
                'status_updated_at' => now(),
            ]);
        }

        session()->flash('success', "Booking {$booking->referenceNumber()} has been marked as Relocated (Walked).");
        $this->dispatch('guest-walked'); // Trigger dashboard refresh
    }

    public $reassignModalOpen = false;
    public $reassignBookingId = null;
    public $reassignData = [];

    public function openReassignModal($bookingId)
    {
        $this->reset(['reassignBookingId', 'reassignData']);
        $booking = Booking::with(['bookingRooms.room', 'bookingRooms.roomType'])->findOrFail($bookingId);
        
        $this->reassignBookingId = $booking->id;
        
        foreach ($booking->bookingRooms as $bRoom) {
            if (!$bRoom->room) continue;

            $floor = substr($bRoom->room->room_number, 0, 1);
            
            $availableRooms = \App\Models\Room::where('room_type_id', $bRoom->room_type_id)
                ->where('current_status', \App\Models\Room::STATUS_AVAILABLE)
                ->where('id', '!=', $bRoom->room_id)
                ->get();
                
            if ($availableRooms->isNotEmpty()) {
                $this->reassignData[$bRoom->id] = [
                    'current_room' => $bRoom->room->room_number,
                    'type' => $bRoom->roomType?->display_name ?? 'Room',
                    'options' => $availableRooms->map(fn($r) => ['id' => $r->id, 'number' => $r->room_number])->toArray(),
                    'selected' => ''
                ];
            }
        }
        
        if (empty($this->reassignData)) {
            session()->flash('error', 'No alternative rooms available for this room type.');
            return;
        }

        $this->reassignModalOpen = true;
    }

    public function submitReassign()
    {
        $booking = Booking::with('bookingRooms.room')->findOrFail($this->reassignBookingId);
        
        $changes = 0;
        foreach ($this->reassignData as $bRoomId => $data) {
            if (!empty($data['selected'])) {
                $bRoom = $booking->bookingRooms->where('id', $bRoomId)->first();
                if ($bRoom && $bRoom->room_id != $data['selected']) {
                    $bRoom->update(['room_id' => $data['selected']]);
                    $changes++;
                }
            }
        }
        
        if ($changes > 0) {
            session()->flash('success', "Successfully reassigned {$changes} room(s).");
        }
        
        $this->reassignModalOpen = false;
    }
}
