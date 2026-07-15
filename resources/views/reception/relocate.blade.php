@extends('layouts.public')

@section('title', 'Relocate Guest — Room ' . ($currentRoom->room_number ?? '?'))

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-2">Guest Room Relocation</h1>
        <p class="text-white/70 text-[0.95rem]">Move a checked-in guest to an alternative room of the same type.</p>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-16 max-w-5xl">

    <div class="mb-6">
        <a href="{{ route('reception.dashboard') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors w-fit">
            <i class="bi bi-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <i class="bi bi-exclamation-triangle text-red-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    {{-- ── Current Booking Summary ─────────────────────────────────────────── --}}
    <div class="bg-amber-50 border border-amber-300 rounded-2xl p-6 mb-8 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="shrink-0 bg-amber-100 rounded-xl p-3">
                <i class="bi bi-exclamation-triangle-fill text-amber-600 text-2xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="font-playfair text-xl font-bold text-amber-900 mb-1">Extension Blocked for Room {{ $currentRoom->room_number }}</h2>
                @if($nextBooking)
                    <p class="text-amber-800 text-[0.95rem]">
                        Another guest is arriving in Room {{ $currentRoom->room_number }} on
                        <strong>{{ $nextBooking->check_in_date?->format('M d, Y') }}</strong>.
                        The current guest cannot extend their stay here.
                    </p>
                @else
                    <p class="text-amber-800 text-[0.95rem]">Extension is not possible on this room. Please select an alternative.</p>
                @endif

                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[0.75rem] text-amber-700 uppercase tracking-wider font-semibold">Guest</p>
                        <p class="font-semibold text-gray-900 text-[0.95rem]">{{ $booking->guest?->full_name ?? 'Walk-in' }}</p>
                    </div>
                    <div>
                        <p class="text-[0.75rem] text-amber-700 uppercase tracking-wider font-semibold">Current Room</p>
                        <p class="font-semibold text-gray-900 text-[0.95rem]">Room {{ $currentRoom->room_number }}</p>
                    </div>
                    <div>
                        <p class="text-[0.75rem] text-amber-700 uppercase tracking-wider font-semibold">Check-In</p>
                        <p class="font-semibold text-gray-900 text-[0.95rem]">{{ $booking->check_in_date?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[0.75rem] text-amber-700 uppercase tracking-wider font-semibold">Check-Out</p>
                        <p class="font-semibold text-gray-900 text-[0.95rem]">{{ $booking->check_out_date?->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Available Alternative Rooms ──────────────────────────────────────── --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark mb-4">
        Available Rooms
        <span class="text-base font-normal text-gray-500 ml-2">— Same type as Room {{ $currentRoom->room_number }}</span>
    </h2>

    @if($alternativeRooms->isEmpty())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center">
            <i class="bi bi-door-closed text-red-400 text-5xl mb-4 block"></i>
            <p class="font-semibold text-red-800 text-lg">No alternative rooms available</p>
            <p class="text-red-700 text-[0.9rem] mt-1">
                There are no other {{ $currentRoom->roomType?->display_name ?? 'rooms of the same type' }} available
                through {{ $booking->check_out_date?->format('M d, Y') }}.
            </p>
            <p class="text-red-700 text-[0.9rem] mt-2">
                Consider discussing an early departure or upgrading the guest to a different room type.
            </p>
        </div>
    @else
        <form action="{{ route('reception.relocate.confirm', $booking->id) }}" method="POST" id="relocate-form">
            @csrf
            <input type="hidden" name="new_room_id" id="selected_room_id" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @foreach($alternativeRooms as $room)
                    <label for="room_{{ $room->id }}"
                        class="relative cursor-pointer group block bg-white rounded-2xl shadow-[0_2px_12px_rgba(0,0,0,0.06)] border-2 border-transparent hover:border-hotel-gold transition-all duration-200 overflow-hidden"
                        x-data="{ selected: false }"
                        @click="
                            document.querySelectorAll('.room-card').forEach(el => el.classList.remove('border-hotel-gold', 'bg-hotel-gold/5'));
                            $el.classList.add('border-hotel-gold', 'bg-hotel-gold/5');
                            document.getElementById('selected_room_id').value = '{{ $room->id }}';
                            document.getElementById('confirm-btn').disabled = false;
                            document.getElementById('confirm-btn').classList.remove('opacity-50', 'cursor-not-allowed');
                        ">
                        <div class="room-card border-2 border-transparent rounded-2xl p-5 h-full">
                            <div class="flex justify-between items-start mb-3">
                                <span class="font-playfair text-2xl font-bold text-hotel-gold">{{ $room->room_number }}</span>
                                <span class="text-[0.75rem] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Available</span>
                            </div>
                            <p class="text-gray-700 font-semibold text-[0.95rem]">{{ $room->roomType?->display_name ?? 'Room' }}</p>
                            <p class="text-gray-500 text-[0.8rem] mt-1">${{ number_format($room->roomType?->price_per_night ?? 0, 2) }} / night</p>
                            @if($room->roomType?->description)
                                <p class="text-gray-400 text-[0.78rem] mt-2 line-clamp-2">{{ $room->roomType->description }}</p>
                            @endif
                            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 text-[0.75rem] text-gray-500">
                                <i class="bi bi-people"></i>
                                {{ $room->roomType?->max_occupancy ?? '?' }} guests max
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>

            {{-- Confirmation Notice --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-[0.85rem] text-blue-800">
                <i class="bi bi-info-circle-fill mr-2 text-blue-500"></i>
                <strong>No extra charge</strong> — the guest keeps their original checkout date ({{ $booking->check_out_date?->format('M d, Y') }}).
                Their original booking will be marked as <em>Relocated</em> and linked to the new booking for your records.
            </div>

            <div class="flex gap-4">
                <button type="submit" id="confirm-btn"
                    onclick="return confirm('Confirm relocation? The guest will be moved immediately and the original room will be freed.')"
                    disabled
                    class="opacity-50 cursor-not-allowed bg-hotel-gold hover:bg-yellow-600 text-white font-bold px-8 py-3 rounded-xl transition-colors shadow-lg shadow-hotel-gold/20 flex items-center gap-2">
                    <i class="bi bi-arrow-repeat"></i>
                    Confirm Relocation
                </button>
                <a href="{{ route('reception.dashboard') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    @endif
</div>

@endsection
