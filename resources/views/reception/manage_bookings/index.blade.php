@extends('layouts.reception')

@section('title', 'Manage Bookings - Reception')
@section('page_title', 'Manage Bookings')

@section('content')
<div class="space-y-6 p-5 md:p-8">
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manual Bookings</h2>
            <p class="text-sm text-gray-500">Search and edit walk-in or phone bookings</p>
        </div>
        <a href="{{ route('reception.manual-booking.create') }}" class="flex items-center gap-2 bg-hotel-gold text-hotel-dark px-4 py-2 rounded-xl font-bold hover:bg-yellow-500 transition-colors shadow-sm">
            <i class="bi bi-plus-lg"></i>
            <span>New Booking</span>
        </a>
    </div>

    @livewire('reception.booking-list')
</div>
@endsection
