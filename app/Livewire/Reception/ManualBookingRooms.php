<?php

namespace App\Livewire\Reception;

use App\Models\Room;
use Livewire\Component;

/**
 * ManualBookingRooms
 *
 * A Livewire component that provides real-time filtering of available rooms
 * and payment auto-calculation on the Manual Booking form.
 * The actual form submission is a standard synchronous POST request that
 * goes through the standard ManualBookingController@store route.
 * This component only manages the dynamic parts:
 *   - Live date inputs → availability list refresh
 *   - Room selection + dates → auto-calculated total & amount due
 */
class ManualBookingRooms extends Component
{
    // Guest Details (Deferred)
    public string $fullName = '';
    public string $phoneNumber = '';
    public string $bookingOrigin = 'walk-in';
    public string $email = '';
    public string $gender = '';
    public ?int $adults = 1;
    public ?int $children = 0;
    public string $nationality = 'Cambodia';
    public string $specialRequests = '';

    // Dates & Payment (Live/Calculated)
    public string $checkInDate  = '';
    public string $checkOutDate = '';
    public array  $selectedRoomIds   = [];
    public float  $pricePerNight     = 0;
    public float  $totalPrice        = 0;
    public int    $nights            = 0;
    public string $paymentTier       = '100';
    public float  $amountDue         = 0;
    public string $paymentMethod     = 'cash';
    public string $availabilityError = '';

    // Room Board Filters (Live)
    public string $viewFilter = '';
    public string $floorFilter = '';
    public string $bedFilter = '';

    // Static Data
    public array $availableViews = [];
    public array $availableFloors = [];
    public array $availableBeds = [];

    public function mount($checkInDate = null, $checkOutDate = null): void
    {
        $this->checkInDate  = $checkInDate ?? today()->toDateString();
        $this->checkOutDate = $checkOutDate ?? today()->addDay()->toDateString();
        
        // Populate static filter options from DB
        $this->availableViews = Room::whereNotNull('view_type')->where('view_type', '!=', '')->distinct()->pluck('view_type')->toArray();
        $this->availableBeds = Room::whereNotNull('bed_configuration')->where('bed_configuration', '!=', '')->distinct()->pluck('bed_configuration')->toArray();
        
        $rooms = Room::select('room_number')->get();
        $floors = $rooms->map(fn($r) => substr($r->room_number, 0, 1))->unique()->sort()->values()->toArray();
        $this->availableFloors = $floors;

        $this->recalculate();
    }

    public function updatedCheckInDate(): void
    {
        if ($this->checkOutDate <= $this->checkInDate) {
            $this->checkOutDate = \Carbon\Carbon::parse($this->checkInDate)->addDay()->toDateString();
        }
        $this->selectedRoomIds = [];
        $this->recalculate();
    }

    public function updatedCheckOutDate(): void
    {
        $this->selectedRoomIds = [];
        $this->recalculate();
    }

    public function updatedAdults(): void { $this->recalculate(); }
    public function updatedChildren(): void { $this->recalculate(); }
    public function updatedViewFilter(): void { $this->recalculate(); }
    public function updatedFloorFilter(): void { $this->recalculate(); }
    public function updatedBedFilter(): void { $this->recalculate(); }
    public function updatedPaymentTier(): void { $this->recalculate(); }
    public function updatedSelectedRoomIds(): void { $this->recalculate(); }

    private function recalculate(): void
    {
        if ($this->checkInDate && $this->checkOutDate) {
            $this->nights = max(1, (int) \Carbon\Carbon::parse($this->checkInDate)
                ->diffInDays(\Carbon\Carbon::parse($this->checkOutDate)));
        }
        $this->availabilityError = '';
        $this->pricePerNight = 0;
        $this->totalPrice = 0;

        if (!empty($this->selectedRoomIds)) {
            $rooms = Room::with('roomType')->whereIn('id', $this->selectedRoomIds)->get();
            
            $errors = [];
            foreach ($rooms as $room) {
                $this->pricePerNight += (float) ($room->roomType?->price_per_night ?? 0);
                
                // Real-time availability check (manual bookings do not use overbooking logic, they are blocked by ANY tier)
                if (!$room->isAvailableForDates($this->checkInDate, $this->checkOutDate, null, 0)) {
                    $errors[] = "Room {$room->room_number} is already booked for these dates.";
                }
            }
            $this->totalPrice = $this->pricePerNight * $this->nights;
            
            if (!empty($errors)) {
                $this->availabilityError = implode(' ', $errors);
            }
        }

        $this->amountDue = round($this->totalPrice * ((int)$this->paymentTier / 100), 2);
        \Illuminate\Support\Facades\Log::info('ManualBookingRooms Recalculate', [
            'roomIds' => $this->selectedRoomIds,
            'pricePerNight' => $this->pricePerNight,
            'nights' => $this->nights,
            'total' => $this->totalPrice,
            'amountDue' => $this->amountDue
        ]);
    }

    private function getAllRoomsData()
    {
        // Get ALL rooms
        $allRooms = Room::with('roomType')->orderBy('room_number')->get();
        
        // Find which ones are available for the date range (0 tier means blocked by ANY existing booking)
        $availableRoomIds = Room::availableForDates($this->checkInDate, $this->checkOutDate, null, 0)->pluck('id')->toArray();

        foreach ($allRooms as $room) {
            $room->is_available_for_dates = in_array($room->id, $availableRoomIds);
            
            // Determine if it matches preference filters (Capacity filter removed for staff multi-room bookings)
            $viewMatch = empty($this->viewFilter) || $room->view_type === $this->viewFilter;
            $bedMatch = empty($this->bedFilter) || $room->bed_configuration === $this->bedFilter;
            $floorMatch = empty($this->floorFilter) || str_starts_with((string)$room->room_number, $this->floorFilter);

            $room->matches_filters = $viewMatch && $bedMatch && $floorMatch;
            
            // A room is selectable if it's available for dates AND matches filters
            $room->is_selectable = $room->is_available_for_dates && $room->matches_filters && $room->current_status !== \App\Models\Room::STATUS_MAINTENANCE;
        }

        return $allRooms;
    }

    public function render()
    {
        $anyFilterActive = !empty($this->bedFilter) || !empty($this->floorFilter) || !empty($this->viewFilter);

        return view('livewire.reception.manual-booking-rooms', [
            'allRooms'        => $this->getAllRoomsData(),
            'anyFilterActive' => $anyFilterActive,
        ]);
    }
}
