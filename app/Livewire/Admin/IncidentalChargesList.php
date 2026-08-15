<?php

namespace App\Livewire\Admin;

use App\Models\IncidentalCharge;
use Livewire\Component;
use Livewire\WithPagination;

class IncidentalChargesList extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $charges = IncidentalCharge::with(['booking.guest', 'booking.room', 'booking.bookingRooms.room'])
            ->where('description', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.admin.incidental-charges-list', [
            'charges' => $charges
        ])->layout('layouts.admin');
    }
}
