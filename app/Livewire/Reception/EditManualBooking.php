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
use App\Models\Booking;

class EditManualBooking extends Component
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

    // Edit Specific
    public Booking $booking;
    public float $originalTotalPaid = 0;
    public float $priceDifference = 0;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking;
        $this->originalTotalPaid = $booking->transactions()->sum('amount_paid');
        
        $this->fullName = $booking->guest->full_name;
        $this->phoneNumber = $booking->guest->phones->first()?->phone_number ?? '';
        $this->bookingOrigin = $booking->booking_origin;
        $this->email = $booking->guest->guestAuth->email ?? '';
        $this->gender = $booking->guest->gender ?? '';
        $this->nationality = $booking->guest->nationality ?? '';
        
        $this->adults = 1; // Assuming 1 if not stored
        $this->children = 0;
        
        $this->checkInDate = \Carbon\Carbon::parse($booking->check_in_date)->toDateString();
        $this->checkOutDate = \Carbon\Carbon::parse($booking->check_out_date)->toDateString();
        $this->selectedRoomIds = $booking->bookingRooms()->pluck('room_id')->toArray();
        
        // Calculate the original payment tier percentage to default the radio buttons
        if ($booking->total_price > 0) {
            $paidRatio = $this->originalTotalPaid / $booking->total_price;
            if ($paidRatio <= 0.25) {
                $this->paymentTier = '20';
            } elseif ($paidRatio <= 0.55) {
                $this->paymentTier = '50';
            } else {
                $this->paymentTier = '100';
            }
        } else {
            $this->paymentTier = '100';
        }
        
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
                
                // Real-time availability check, excluding current booking
                if (!$room->isAvailableForDates($this->checkInDate, $this->checkOutDate, $this->booking->id, 0)) {
                    $errors[] = "Room {$room->room_number} is already booked for these dates.";
                }
            }
            $this->totalPrice = $this->pricePerNight * $this->nights;
            
            if (!empty($errors)) {
                $this->availabilityError = implode(' ', $errors);
            }
            
            // For modifications, Amount Due is the difference
            $tierMultiplier  = ((float) $this->paymentTier) / 100;
            $requiredAmount  = $this->totalPrice * $tierMultiplier;
            $this->amountDue = max(0, $requiredAmount - $this->originalTotalPaid);
            $this->priceDifference = $this->totalPrice - $this->originalTotalPaid;
        } else {
            $this->amountDue = 0;
            $this->priceDifference = 0 - $this->originalTotalPaid;
        }
    }

    private function getAllRoomsData()
    {
        // Get ALL rooms
        $allRooms = Room::with('roomType')->orderBy('room_number')->get();
        
        // Find which ones are available for the date range (0 tier means blocked by ANY existing booking)
        $availableRoomIds = Room::availableForDates($this->checkInDate, $this->checkOutDate, $this->booking->id, 0)->pluck('id')->toArray();

        foreach ($allRooms as $room) {
            $room->is_available_for_dates = in_array($room->id, $availableRoomIds);
            
            // Determine if it matches preference filters (Capacity filter removed for staff bookings)
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

        return view('livewire.reception.edit-manual-booking', [
            'allRooms'        => $this->getAllRoomsData(),
            'anyFilterActive' => $anyFilterActive,
        ]);
    }
}
