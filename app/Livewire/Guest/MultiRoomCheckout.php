<?php

namespace App\Livewire\Guest;

use Livewire\Component;
use App\Models\RoomType;
use Carbon\Carbon;

class MultiRoomCheckout extends Component
{
    public $cart = []; // Array of associative arrays
    public $checkin;
    public $checkout;
    public $nights = 1;
    public $grandTotal = 0;
    public $selectedTier = 100;
    public $khrRate = 4100;
    public $hasNoDepositBooking = false;

    // Cache to display available room types below
    public $availableRoomTypes = [];
    public $allRoomTypes = [];

    public function mount($initialCart, $checkin, $checkout, $allRoomTypes, $khrRate, $hasNoDepositBooking)
    {
        $this->checkin = $checkin;
        $this->checkout = $checkout;
        
        $this->allRoomTypes = collect($allRoomTypes)->map(function ($rt) {
            $arr = $rt->toArray();
            $arr['availableBeds'] = $rt->availableBeds ?? [];
            $arr['availableViews'] = $rt->availableViews ?? [];
            $arr['availableFloors'] = $rt->availableFloors ?? [];
            return $arr;
        })->toArray();

        $this->khrRate = $khrRate;
        $this->hasNoDepositBooking = $hasNoDepositBooking;

        foreach ($initialCart as $item) {
            $this->cart[] = [
                'id' => $item['roomType']->id,
                'slug' => $item['roomType']->slug,
                'name' => $item['roomType']->display_name,
                'qty' => $item['qty'],
                'price' => (float)$item['roomType']->price_per_night,
            ];
        }

        $this->recalculateTotal();
        $this->refreshAvailableRoomTypes();
    }

    public function updatedCheckin()
    {
        $this->recalculateTotal();
        $this->validateCartAvailability();
    }

    public function updatedCheckout()
    {
        $this->recalculateTotal();
        $this->validateCartAvailability();
    }

    public function recalculateTotal()
    {
        // Prevent typing past dates. 
        // Note: Browsers often return an empty string "" if the user manually types a date 
        // that violates the HTML5 min="" attribute. We catch empty strings here to force the snap.
        if (empty($this->checkin) || $this->checkin < date('Y-m-d')) {
            $this->checkin = date('Y-m-d');
        }

        if (empty($this->checkout) || $this->checkout <= $this->checkin) {
            $this->checkout = \Carbon\Carbon::parse($this->checkin)->addDay()->toDateString();
        }

        if ($this->checkin && $this->checkout) {
            $start = Carbon::parse($this->checkin);
            $end = Carbon::parse($this->checkout);
            $this->nights = max(1, $start->diffInDays($end));
        } else {
            $this->nights = 1;
        }

        $perNight = 0;
        foreach ($this->cart as $item) {
            $perNight += $item['price'] * ($item['qty'] ?? 1);
        }
        $this->grandTotal = $perNight * $this->nights;

        $totalRooms = collect($this->cart)->sum('qty');
        $isAdvanceBooking = false;
        if ($this->checkin) {
            $diffDays = now()->startOfDay()->diffInDays(Carbon::parse($this->checkin)->startOfDay(), false);
            $isAdvanceBooking = $diffDays > 3;
        }

        if ($this->selectedTier == 0 && ($totalRooms > 2 || $this->hasNoDepositBooking || $isAdvanceBooking)) {
            $this->selectedTier = 20;
        }
    }

    public function validateCartAvailability()
    {
        // For each item in cart, verify availability. If unavailable, remove it and flash error.
        $newCart = [];
        $removedRooms = [];
        foreach ($this->cart as $item) {
            // Need to fetch fresh from DB because $allRoomTypes is just a collection of original models, and we need to run hasAvailableVirtualCapacity
            $roomType = RoomType::find($item['id']);
            if ($roomType && $roomType->hasAvailableVirtualCapacity($this->checkin, $this->checkout, 100)) {
                $newCart[] = $item;
            } else {
                $removedRooms[] = $item['name'];
            }
        }
        if (!empty($removedRooms)) {
            session()->flash('error', implode(", ", $removedRooms) . " no longer available for the selected dates and have been removed from your cart.");
        }
        $this->cart = $newCart;
        $this->recalculateTotal();
        $this->refreshAvailableRoomTypes();
    }

    public function refreshAvailableRoomTypes()
    {
        $inCartSlugs = collect($this->cart)->pluck('slug')->toArray();
        $this->availableRoomTypes = collect($this->allRoomTypes)->filter(function ($rt) use ($inCartSlugs) {
            return !in_array($rt['slug'], $inCartSlugs);
        })->values()->toArray();
    }

    public function addToCart($slug)
    {
        if (collect($this->cart)->contains('slug', $slug)) return;

        $roomType = RoomType::where('slug', $slug)->first();
        if (!$roomType) return;

        if (!$roomType->hasAvailableVirtualCapacity($this->checkin, $this->checkout, 100)) {
            session()->flash('error', "This room type is fully booked for the selected dates.");
            return;
        }

        $this->cart[] = [
            'id' => $roomType->id,
            'slug' => $roomType->slug,
            'name' => $roomType->display_name,
            'qty' => 1,
            'price' => (float)$roomType->price_per_night,
        ];

        $this->recalculateTotal();
        $this->refreshAvailableRoomTypes();
    }

    public function removeFromCart($slug)
    {
        $this->cart = collect($this->cart)->reject(function ($item) use ($slug) {
            return $item['slug'] === $slug;
        })->values()->toArray();

        $this->recalculateTotal();
        $this->refreshAvailableRoomTypes();
    }

    public function render()
    {
        return view('livewire.guest.multi-room-checkout');
    }
}
