<?php

namespace App\Livewire\Reception;

use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class BookingList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage('activePage');
        $this->resetPage('historyPage');
    }

    public function render()
    {
        $baseQuery = Booking::with(['guest', 'bookingRooms.roomType'])
            ->whereIn('booking_origin', ['walk-in', 'phone', 'other'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', "%{$this->search}%")
                      ->orWhereHas('guest', function ($q2) {
                          $q2->where('full_name', 'like', "%{$this->search}%");
                      })
                      ->orWhereHas('guest.phones', function ($q3) {
                          $q3->where('phone_number', 'like', "%{$this->search}%");
                      })
                      ->orWhereHas('transactions', function ($t) {
                          $t->where('payment_reference', 'like', "%{$this->search}%");
                      });
                });
            });

        $activeBookings = (clone $baseQuery)->where(function ($q) {
            $q->whereIn('booking_status', ['pending', 'booked'])
              ->orWhere(function ($q2) {
                  $q2->where('booking_status', 'cancelled')
                     ->whereDate('check_in_date', '>=', Carbon::today());
              });
        })->latest()->paginate(10, ['*'], 'activePage');

        $historyBookings = (clone $baseQuery)->where(function ($q) {
            $q->whereIn('booking_status', ['checked-in', 'checked-out', 'no_show'])
              ->orWhere(function ($q2) {
                  $q2->where('booking_status', 'cancelled')
                     ->whereDate('check_in_date', '<', Carbon::today());
              });
        })->latest()->paginate(10, ['*'], 'historyPage');

        return view('livewire.reception.booking-list', compact('activeBookings', 'historyBookings'));
    }
}
