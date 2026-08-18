<?php

namespace App\Livewire\Cleaner;

use App\Models\IncidentalCharge;
use App\Models\IncidentalItem;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RoomCheck extends Component
{
    public string $flashMessage = '';
    public string $flashType    = 'success';

    // ── Tracks which room IDs have been inspected this session ───────────────
    // Persists as a Livewire property so the button stays greyed on re-render.
    public array $inspectedRoomIds = [];

    /**
     * Submit the damage report from the cleaner's modal.
     *
     * Three paths:
     *  1. No Damage — just marks the room as inspected (button greys out).
     *  2. Damage with item — creates IncidentalCharge from catalog item.
     *  3. Damage custom — creates IncidentalCharge from free-text.
     */
    public function submitDamageReport(int $roomId, string $roomNumber, ?int $bookingId, array $itemQuantities, string $damageNotes, bool $noDamage): void
    {
        if ($noDamage) {
            // No damage path — simply record that the room was inspected.
            $this->inspectedRoomIds[] = $roomId;
            $this->flashMessage = "Room {$roomNumber}: No damage reported. Inspection recorded.";
            $this->flashType    = 'success';
            $this->dispatch('damage-modal-closed');
            return;
        }

        if (!$bookingId) {
            $this->addError('bookingId', 'Cannot link charge — no active booking found for this room.');
            return;
        }

        // Filter out items with 0 quantity
        $activeItems = collect($itemQuantities)->filter(fn($qty) => $qty > 0);
        $hasNotes    = trim($damageNotes) !== '';

        if ($activeItems->isEmpty() && !$hasNotes) {
            $this->addError('damage', 'Please select at least one damaged item or provide additional notes.');
            return;
        }

        $staffId = Auth::guard('staff')->id();
        $totalAddedToBooking = 0;

        // Process standard items
        if ($activeItems->isNotEmpty()) {
            $catalogItems = IncidentalItem::whereIn('id', $activeItems->keys())->get()->keyBy('id');
            
            foreach ($activeItems as $itemId => $quantity) {
                $item = $catalogItems->get($itemId);
                if (!$item) continue;

                $lineTotal = $item->default_amount * $quantity;
                
                IncidentalCharge::create([
                    'booking_id'           => $bookingId,
                    'room_id'              => $roomId,
                    'reported_by_staff_id' => $staffId,
                    'description'          => $item->name,
                    'quantity'             => $quantity,
                    'amount'               => $item->default_amount,
                    'total_amount'         => $lineTotal,
                ]);
                
                $totalAddedToBooking += $lineTotal;
            }
        }

        // Create a $0 charge for custom notes if provided so Receptionist can see it
        if ($hasNotes) {
            IncidentalCharge::create([
                'booking_id'           => $bookingId,
                'room_id'              => $roomId,
                'reported_by_staff_id' => $staffId,
                'description'          => 'Cleaner Note: ' . trim($damageNotes),
                'quantity'             => 1,
                'amount'               => 0,
                'total_amount'         => 0,
            ]);
        }

        // Bump the booking's total_price
        if ($totalAddedToBooking > 0) {
            \App\Models\Booking::find($bookingId)?->increment('total_price', $totalAddedToBooking);
        }

        $this->inspectedRoomIds[] = $roomId;
        $this->flashMessage = "Room {$roomNumber}: Damage report submitted to Reception.";
        $this->flashType    = 'success';
        $this->dispatch('damage-modal-closed');
    }

    public function markAvailable(Room $room): void
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
        $cleaningRooms    = Room::with(['roomType', 'activeBookings'])->cleaning()->orderBy('room_number')->get();
        $maintenanceRooms = Room::with('roomType')->maintenance()->orderBy('room_number')->get();
        $availableRooms   = Room::with('roomType')->available()->orderBy('room_number')->get();
        
        $allOccupiedRooms = Room::with(['roomType', 'activeBookings'])->occupied()->orderBy('room_number')->get();
        
        $dueOutRooms = collect();
        $stayoverRooms = collect();
        $today = \Carbon\Carbon::today();

        foreach ($allOccupiedRooms as $room) {
            $activeBooking = $room->activeBookings->first();
            if ($activeBooking && \Carbon\Carbon::parse($activeBooking->check_out_date)->startOfDay()->lte($today)) {
                $dueOutRooms->push($room);
            } else {
                $stayoverRooms->push($room);
            }
        }
            
        $incidentalItems  = IncidentalItem::active()->orderBy('name')->get();

        return view('livewire.cleaner.room-check', compact(
            'cleaningRooms',
            'maintenanceRooms',
            'availableRooms',
            'dueOutRooms',
            'stayoverRooms',
            'incidentalItems',
        ));
    }
}
