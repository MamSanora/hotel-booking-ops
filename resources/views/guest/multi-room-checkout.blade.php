@extends('layouts.public')

@section('title', 'Book Multiple Rooms - ' . config('app.name'))

@section('content')
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 lg:py-16 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>
    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-3xl lg:text-[2.2rem] font-bold text-white mb-2">Complete Your Multi-Room Booking</h1>
        <p class="mt-2 text-white/80">Review your selected rooms and finalize your reservation.</p>
    </div>
</div>

@livewire('guest.multi-room-checkout', [
    'initialCart' => $cartItems,
    'checkin' => $checkin,
    'checkout' => $checkout,
    'allRoomTypes' => $allRoomTypes,
    'khrRate' => $exchangeRate ?? 4100,
    'hasNoDepositBooking' => $hasNoDepositBooking,
    'existingBookingId' => $existingBookingId ?? null
])

<div class="container mx-auto px-4 md:px-6 mb-12">
    {{-- Room Detail Modals for all room types (Keep using Alpine for the modal toggle) --}}
    <div x-data="{ activeModal: null }" @open-room-modal.window="activeModal = $event.detail.id">
        @foreach($allRoomTypes as $rt)
            <div x-show="activeModal === {{ $rt->id }}" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="activeModal === {{ $rt->id }}"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="activeModal = null"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="activeModal === {{ $rt->id }}"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                         class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-start">
                                <h3 class="text-2xl leading-6 font-bold text-hotel-dark font-playfair">{{ $rt->display_name }}</h3>
                                <button type="button" @click="activeModal = null" class="text-gray-400 hover:text-gray-500"><i class="bi bi-x-lg"></i></button>
                            </div>
                            <div class="mt-4">
                                @php
                                    $fallbackImages = [
                                        'standard_room' => asset('room/Standard Double 1.jpg'),
                                        'deluxe_room' => asset('room/Deluxe Double 1.webp'),
                                        'family_triple_room' => asset('room/Family Triple Room 1.webp'),
                                    ];
                                    $rtImgs = is_string($rt->images) ? json_decode($rt->images, true) : $rt->images;
                                    if (is_array($rtImgs) && count($rtImgs) > 0) {
                                        $rtImg = asset('room/' . $rtImgs[0]);
                                    } else {
                                        $rtImg = $fallbackImages[$rt->slug] ?? null;
                                    }
                                @endphp
                                @if($rtImg)
                                    <img src="{{ $rtImg }}" alt="{{ $rt->display_name }}" class="w-full h-48 object-cover rounded-xl mb-4">
                                @endif
                                <p class="text-sm text-gray-500 mb-4">{{ $rt->description }}</p>
                                <div class="flex items-center space-x-4 text-[0.85rem] font-medium text-gray-600">
                                    <span class="flex items-center"><i class="bi bi-people text-hotel-gold mr-1.5"></i> {{ $rt->adult_capacity }} Adults, {{ $rt->child_capacity }} Children</span>
                                    <span class="flex items-center"><i class="bi bi-arrows-fullscreen text-hotel-gold mr-1.5 text-xs"></i> {{ $rt->size_sqm }} m&#xB2;</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-2xl">
                            <button type="button" @click="activeModal = null"
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-hotel-gold text-base font-medium text-white hover:bg-[#a68249] sm:ml-3 sm:w-auto sm:text-sm">
                                Close Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
