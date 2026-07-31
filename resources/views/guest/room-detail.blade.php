@extends('layouts.public')

@section('title', $room->displayType())

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 lg:py-16 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>

    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-3xl lg:text-[2.2rem] font-bold text-white mb-2">
            {{ $room->displayType() }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="flex space-x-2 text-sm text-white/60">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a></li>
                <li class="text-white/30">/</li>
                <li><a href="{{ route('rooms.index') }}" class="hover:text-white transition-colors">Rooms</a></li>
                <li class="text-white/30">/</li>
                <li class="text-hotel-gold" aria-current="page">{{ $room->displayType() }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12">

        {{-- ==========================================
             LEFT: Room Info
             ========================================== --}}
        <div class="lg:col-span-7">

            {{-- Main Room Image --}}
            @php
                $slug = $room->roomType?->slug;
                $roomDetailImages = [
                    'standard_room' => [
                        asset('room/Standard Double 1.jpg'),
                        asset('room/Standard Double 2.webp'),
                        asset('room/Standard Double 3.jpg'),
                        asset('room/Standard Twin 1.webp'),
                        asset('room/Standard Twin 2.webp'),
                        asset('room/Standard Twin 3.webp'),
                        asset('room/Standard Twin 4.png'),
                        asset('room/Standard Bathroom 1.jpg'),
                        asset('room/Standard Bathroom 2.jpg'),
                        asset('room/Standard Bathroom 3.jpg'),
                        asset('room/Standard bathroom 4.jpg'),
                        asset('room/Balcony 1.webp'),
                    ],
                    'deluxe_room' => [
                        asset('room/Deluxe Double 1.webp'),
                        asset('room/Deluxe Double 2.webp'),
                        asset('room/Deluxe Double 3.webp'),
                        asset('room/Deluxe Double 4.webp'),
                        asset('room/Deluxe Double 5.webp'),
                        asset('room/Deluxe Double 6.webp'),
                        asset('room/Deluxe Double 7.webp'),
                        asset('room/Deluxe Double 8.webp'),
                        asset('room/Deluxe Double 9.webp'),
                        asset('room/Deluxe Double 10.webp'),
                        asset('room/Deluxe Twin 1.webp'),
                        asset('room/Deluxe Twin 2.webp'),
                        asset('room/Deluxe Twin 3.webp'),
                        asset('room/Deluxe Twin 4.webp'),
                        asset('room/Deluxe Bathroom 1.webp'),
                        asset('room/Deluxe Bathroom 2.webp'),
                        asset('room/Balcony 1.webp'),
                    ],
                    'family_triple_room' => [
                        asset('room/Family Triple Room 1.webp'),
                        asset('room/Family Triple Room 2.webp'),
                        asset('room/Family Triple Room 3.webp'),
                        asset('room/Family Triple Room 4.webp'),
                        asset('room/Family Triple Room 5.webp'),
                        asset('room/Family Triple Room 6.webp'),
                        asset('room/Deluxe Bathroom 1.webp'),
                        asset('room/Deluxe Bathroom 2.webp'),
                        asset('room/Balcony 1.webp'),
                    ],
                    'test_room' => [],
                ];
                $imgs = $roomDetailImages[$slug] ?? [];
                $roomsLeft = $room->roomType?->getAvailableCount(request('checkin'), request('checkout')) ?? 0;
            @endphp

            {{-- Image Slider --}}
            @if(count($imgs) > 0)
            <div class="relative overflow-hidden rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] mb-8"
                 x-data="{ current: 0, imgs: {{ json_encode($imgs) }} }">
                <template x-for="(img, i) in imgs" :key="i">
                    <img :src="img" :alt="'{{ $room->displayType() }} ' + (i+1)"
                         class="absolute inset-0 w-full h-[300px] sm:h-[400px] object-cover transition-opacity duration-500"
                         :class="i === current ? 'opacity-100' : 'opacity-0'">
                </template>
                {{-- Spacer to maintain height --}}
                <div class="h-[300px] sm:h-[400px]"></div>
                {{-- Prev/Next --}}
                <button type="button" @click="current = (current - 1 + imgs.length) % imgs.length"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 flex items-center justify-center bg-black/40 hover:bg-black/60 text-white rounded-full transition">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" @click="current = (current + 1) % imgs.length"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 flex items-center justify-center bg-black/40 hover:bg-black/60 text-white rounded-full transition">
                    <i class="bi bi-chevron-right"></i>
                </button>
                {{-- Dot Indicators --}}
                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-20">
                    <template x-for="(img, i) in imgs" :key="i">
                        <button type="button" @click="current = i"
                                :class="i === current ? 'bg-white w-5' : 'bg-white/50 w-2.5'"
                                class="h-2.5 rounded-full transition-all duration-300"></button>
                    </template>
                </div>
            </div>
            @else
            {{-- Test Room: no images --}}
            <div class="relative overflow-hidden rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.12)] mb-8 bg-gradient-to-br from-gray-100 to-gray-200 h-[300px] sm:h-[400px] flex items-center justify-center">
                <div class="text-center text-gray-400">
                    <i class="bi bi-door-closed text-7xl block mb-3"></i>
                    <span class="text-sm font-medium">Test Room — No Images</span>
                </div>
            </div>
            @endif

            {{-- Room Basics --}}
            <div class="flex flex-wrap gap-2.5 mb-8">
                {{-- Hide available rooms badge temporarily as requested by the user
                @if($roomsLeft > 0)
                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[0.82rem] font-bold px-3.5 py-1.5 rounded-lg">
                        <i class="bi bi-check-circle-fill text-emerald-600"></i>Available &middot; {{ $roomsLeft }} {{ Str::plural('room', $roomsLeft) }} left
                    </span>
                @endif
                --}}
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-people text-hotel-gold"></i>Up to {{ $room->roomType?->capacity }} guests
                </span>
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-wifi text-hotel-gold"></i>Free Wi-Fi
                </span>
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-snow text-hotel-gold"></i>Air Conditioning
                </span>
                <span class="inline-flex items-center gap-1.5 bg-hotel-light border border-[#e8e0d0] text-hotel-dark text-[0.82rem] font-medium px-3.5 py-1.5 rounded-lg">
                    <i class="bi bi-tv text-hotel-gold"></i>Flat-Screen TV
                </span>
            </div>

            {{-- Price --}}
            <div class="flex items-end gap-4 mb-8">
                <div class="font-playfair text-[2.8rem] font-bold text-hotel-gold leading-none">
                    ${{ number_format($room->roomType?->price_per_night ?? 0, 0) }}
                    <span class="text-base text-gray-400 font-sans font-normal">/night</span>
                </div>
                <span class="bg-hotel-gold/10 text-[#b8935a] text-sm font-semibold px-4 py-2 rounded-xl mb-1">
                    {{ $room->displayType() }}
                </span>
            </div>

            {{-- Description --}}
            <h5 class="font-bold text-xl text-hotel-dark mb-4">About This Room</h5>
            <p class="text-gray-600 leading-[1.9] text-[0.95rem] mb-10">
                {{ $room->roomType?->description ?? 'A comfortable and well-appointed room at Dara Meas Hotel, Phnom Penh.' }}
            </p>

            {{-- What's Included --}}
            <h5 class="font-bold text-xl text-hotel-dark mb-4">What's Included</h5>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6 mb-12">
                @foreach([
                    'Free high-speed Wi-Fi',
                    'Air conditioning',
                    'Flat-screen TV',
                    'Private bathroom with hot shower',
                    'Daily housekeeping',
                    'Fresh towels & toiletries',
                    'In-room safe',
                    'Mini fridge',
                ] as $item)
                    <div class="flex items-center gap-2.5 text-[0.9rem] text-gray-700">
                        <i class="bi bi-check-circle-fill text-hotel-gold"></i>
                        {{ $item }}
                    </div>
                @endforeach
            </div>

            {{-- Hotel Policies --}}
            <div class="bg-hotel-light rounded-xl p-6 md:p-8">
                <h5 class="font-bold text-lg text-hotel-dark mb-5 flex items-center gap-2">
                    <i class="bi bi-info-circle text-hotel-gold"></i> Hotel Policies
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[0.88rem] text-gray-600">
                    <div><strong class="text-gray-800 font-semibold">Check-In:</strong> From 14:00 (2:00 PM)</div>
                    <div><strong class="text-gray-800 font-semibold">Check-Out:</strong> Before 12:00 (Noon)</div>

                    <div><strong class="text-gray-800 font-semibold">Payment:</strong> ABA PayWay QR Code</div>
                </div>
            </div>
        </div>

        {{-- ==========================================
             RIGHT: Booking Form
             ========================================== --}}
        <div class="lg:col-span-5">
            <div class="bg-white rounded-[18px] shadow-[0_8px_40px_rgba(0,0,0,0.12)] p-6 md:p-8 sticky top-24">
                <h4 class="font-playfair text-[1.5rem] font-bold text-hotel-dark mb-6 pb-4 border-b-2 border-[#f0ebe2] flex items-center">
                    <i class="bi bi-calendar-plus mr-3 text-hotel-gold"></i>Book This Room
                </h4>

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="bg-red-50 text-red-800 border border-red-200 rounded-lg p-4 mb-6">
                        <ul class="list-disc list-inside text-[0.88rem] space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('message'))
                    <div class="bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg p-4 mb-6 text-[0.88rem] flex items-start">
                        <i class="bi bi-exclamation-triangle mr-2 mt-0.5 text-yellow-600"></i> {{ session('message') }}
                    </div>
                @endif

                @auth('web')
                    {{-- Guest info preview (read-only) --}}
                    @php $guestProfile = Auth::guard('web')->user()->guest; @endphp
                    @if($guestProfile)
                        <div class="bg-hotel-light rounded-xl p-4 mb-5 flex items-center gap-3 border border-[#e8e0d0]">
                            <i class="bi bi-person-check-fill text-hotel-gold text-xl"></i>
                            <div>
                                <div class="text-sm font-semibold text-hotel-dark">{{ $guestProfile->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::guard('web')->user()->email }}</div>
                            </div>
                            <a href="{{ route('guest.profile.edit') }}" class="ml-auto text-xs text-hotel-gold hover:underline">Edit</a>
                        </div>
                    @endif

                    <form action="{{ route('booking.store', $room) }}" method="POST" id="bookingForm" class="space-y-4">
                        @csrf

                        {{-- Dates --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-1.5">Check-In Date</label>
                                <input type="date" name="check_in_date" id="check_in_date"
                                       min="{{ date('Y-m-d') }}"
                                       value="{{ old('check_in_date', request('checkin')) }}" required
                                       class="w-full border-[1.5px] border-gray-200 rounded-lg px-3.5 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
                            </div>
                            <div>
                                <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-1.5">Check-Out Date</label>
                                <input type="date" name="check_out_date" id="check_out_date"
                                       value="{{ old('check_out_date', request('checkout')) }}" required
                                       class="w-full border-[1.5px] border-gray-200 rounded-lg px-3.5 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
                            </div>
                        </div>

                        {{-- Special Requests --}}
                        <div>
                            <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-1.5">
                                Special Requests <span class="font-normal lowercase normal-case">(optional)</span>
                            </label>
                            <textarea name="special_requests" rows="2"
                                      placeholder="e.g. Extra pillows, early check-in..."
                                      class="w-full border-[1.5px] border-gray-200 rounded-lg px-3.5 py-2.5 text-[0.95rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none resize-none">{{ old('special_requests') }}</textarea>
                        </div>

                        {{-- Guest Preferences --}}
                        @if($availableBeds->count() > 0 || $availableFloors->count() > 0 || $availableViews->count() > 0)
                        <div class="mb-4">
                            <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-3">Room Preferences <span class="font-normal normal-case lowercase">(optional)</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                {{-- Bed Type --}}
                                @if($availableBeds->count() > 1)
                                <div>
                                    <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Bed Type</label>
                                    <select name="bed_type" id="bed_type" class="w-full border-[1.5px] border-gray-200 rounded-lg pl-3 pr-8 py-2.5 text-[0.9rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%236b7280%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem top 50%; background-size: 0.65rem auto;">
                                        <option value="">No Preference</option>
                                        @foreach($availableBeds as $bed)
                                            <option value="{{ $bed }}" {{ old('bed_type') === $bed ? 'selected' : '' }}>
                                                {{ ucfirst($bed) . ' Bed' . (in_array($bed, ['twin', 'triple']) ? 's' : '') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @elseif($availableBeds->count() === 1)
                                <div>
                                    <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Bed Type</label>
                                    <div class="w-full border-[1.5px] border-gray-100 bg-gray-50 rounded-lg px-3 py-2.5 text-[0.9rem] text-gray-500 font-medium whitespace-nowrap overflow-hidden text-ellipsis">
                                        <i class="bi bi-check2-circle text-hotel-gold mr-1"></i> 
                                        @php $bed = $availableBeds->first(); @endphp
                                        {{ ucfirst($bed) . ' Bed' . (in_array($bed, ['twin', 'triple']) ? 's' : '') }}
                                    </div>
                                </div>
                                @endif

                                {{-- Floor --}}
                                @if($availableFloors->count() > 1)
                                <div>
                                    <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Floor</label>
                                    <select name="floor_preference" id="floor_preference" class="w-full border-[1.5px] border-gray-200 rounded-lg pl-3 pr-8 py-2.5 text-[0.9rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%236b7280%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem top 50%; background-size: 0.65rem auto;">
                                        <option value="">No Preference</option>
                                        @foreach($availableFloors as $floor)
                                            <option value="{{ $floor }}" {{ old('floor_preference') == $floor ? 'selected' : '' }}>Floor {{ $floor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @elseif($availableFloors->count() === 1)
                                <div>
                                    <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Floor</label>
                                    <div class="w-full border-[1.5px] border-gray-100 bg-gray-50 rounded-lg px-3 py-2.5 text-[0.9rem] text-gray-500 font-medium">
                                        <i class="bi bi-check2-circle text-hotel-gold mr-1"></i> Floor {{ $availableFloors->first() }}
                                    </div>
                                </div>
                                @endif

                                {{-- View --}}
                                @if($availableViews->count() > 1)
                                <div>
                                    <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">View</label>
                                    <select name="view_preference" id="view_preference" class="w-full border-[1.5px] border-gray-200 rounded-lg pl-3 pr-8 py-2.5 text-[0.9rem] focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white appearance-none cursor-pointer" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%236b7280%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem top 50%; background-size: 0.65rem auto;">
                                        <option value="">No Preference</option>
                                        @foreach($availableViews as $view)
                                            <option value="{{ $view }}" {{ old('view_preference') === $view ? 'selected' : '' }}>{{ ucfirst($view) }} View</option>
                                        @endforeach
                                    </select>
                                </div>
                                @elseif($availableViews->count() === 1)
                                <div>
                                    <label class="block text-[0.75rem] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">View</label>
                                    <div class="w-full border-[1.5px] border-gray-100 bg-gray-50 rounded-lg px-3 py-2.5 text-[0.9rem] text-gray-500 font-medium">
                                        <i class="bi bi-check2-circle text-hotel-gold mr-1"></i> {{ ucfirst($availableViews->first()) }}
                                    </div>
                                </div>
                                @endif
                            </div>
                            <p class="text-[0.72rem] text-gray-400 mt-2"><i class="bi bi-info-circle"></i> Preferences are requests and subject to availability at check-in.</p>
                        </div>
                        @endif
                        
                        {{-- Preference Availability Warning Container --}}
                        <div id="preferenceWarning" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-amber-500 mt-0.5"></i>
                                <div>
                                    <h5 class="text-amber-800 font-bold text-[0.9rem] mb-1">Preferences Unavailable</h5>
                                    <p class="text-amber-700 text-[0.82rem] leading-relaxed">
                                        We don't have a physical room matching all these exact preferences available for these dates. You can still proceed with your booking, but we may not be able to guarantee this request.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Fully Booked Warning Container --}}
                        <div id="fullyBookedWarning" class="hidden bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-x-circle-fill text-red-500 mt-0.5"></i>
                                <div>
                                    <h5 class="text-red-800 font-bold text-[0.9rem] mb-1">Fully Booked</h5>
                                    <p class="text-red-700 text-[0.82rem] leading-relaxed">
                                        This room type is fully booked for the selected dates and payment option. Please choose different dates or another room type.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Price Summary --}}
                        <div class="bg-hotel-light rounded-xl p-5 my-6 space-y-2.5 border border-[#e8e0d0]" id="priceSummary">
                            <div class="flex justify-between text-[0.9rem] text-gray-600">
                                <span>Rate per night</span>
                                <span class="font-medium text-gray-800">${{ number_format($room->roomType?->price_per_night ?? 0, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-[0.9rem] text-gray-600">
                                <span>Number of nights</span>
                                <span id="nightCount" class="font-medium text-gray-800">&mdash;</span>
                            </div>
                            <div class="flex justify-between items-center text-[1.08rem] font-bold text-hotel-dark border-t border-[#e0d8cc] pt-3 mt-3">
                                <span>Estimated Total (USD)</span>
                                <span id="totalPrice" class="text-hotel-gold text-xl">&mdash;</span>
                            </div>
                            <div class="flex justify-between items-center text-[0.82rem] font-semibold text-gray-500 bg-white/60 px-3 py-1.5 rounded-lg border border-gray-200">
                                <span>Approx. KHR Equivalent (៛)</span>
                                <span id="khrPrice" class="text-gray-700 font-bold">&mdash;</span>
                            </div>
                            <div class="flex justify-between items-center text-[0.95rem] font-semibold text-hotel-dark pt-1" id="depositRow" style="display:none !important">
                                <span>Amount Due Today</span>
                                <span id="depositAmount" class="text-green-700">&mdash;</span>
                            </div>
                            <div class="flex justify-between items-center text-[0.82rem] text-gray-500" id="balanceRow" style="display:none !important">
                                <span>Remaining Balance (at check-in)</span>
                                <span id="balanceAmount">&mdash;</span>
                            </div>
                            <div class="pt-2 border-t border-[#e0d8cc]/60 flex items-center gap-1.5 text-[0.76rem] text-emerald-700 font-medium">
                                <i class="bi bi-shield-check text-base"></i>
                                <span>Taxes & basic amenities included · No hidden fees.</span>
                            </div>
                        </div>

                        {{-- Payment Tier Selector --}}
                        <div class="mb-6">
                            <label class="block font-semibold text-[0.8rem] uppercase text-gray-500 tracking-wider mb-3">Payment Option</label>
                            <div class="space-y-2.5">
                                @foreach([
                                    ['value' => 100, 'label' => 'Full Payment',     'desc'  => 'Pay the full amount now and your reservation is fully secured.'],
                                    ['value' => 50,  'label' => '50% Deposit',      'desc'  => 'Pay 50% now, settle the remaining balance at check-in.'],
                                    ['value' => 20,  'label' => '20% Deposit',      'desc'  => 'Pay 20% now to hold your reservation, balance due at check-in.'],
                                ] as $tier)
                                    <label class="flex items-start gap-3 border-[1.5px] rounded-xl px-4 py-3.5 cursor-pointer transition-all border-gray-200 hover:border-hotel-gold has-[:checked]:border-hotel-gold has-[:checked]:bg-[#fffbf0]">
                                        <input type="radio"
                                               name="payment_tier"
                                               value="{{ $tier['value'] }}"
                                               id="tier_{{ $tier['value'] }}"
                                               {{ old('payment_tier', 100) == $tier['value'] ? 'checked' : '' }}
                                               class="mt-0.5 accent-hotel-gold shrink-0 tier-radio">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="font-semibold text-hotel-dark text-[0.9rem]">{{ $tier['label'] }}</span>
                                            </div>
                                            <p class="text-[0.78rem] text-gray-500 mt-0.5">{{ $tier['desc'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('payment_tier')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Payment Method (forced to khqr_aba; hidden from guest) --}}
                        <input type="hidden" name="payment_method" value="khqr_aba">


                        {{-- Terms and Conditions --}}
                        <div class="mb-5 flex items-start gap-2.5">
                            <input type="checkbox" id="terms" name="terms" required class="mt-0.5 w-4 h-4 text-hotel-gold border-gray-300 rounded focus:ring-hotel-gold cursor-pointer">
                            <label for="terms" class="text-[0.85rem] text-gray-700 cursor-pointer">
                                I have read and understood the <a href="#" class="font-bold text-blue-700 hover:text-blue-800 underline underline-offset-2">Booking policy and Terms & conditions</a>
                            </label>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-bold rounded-xl py-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(200,169,110,0.45)] flex justify-center items-center gap-2">
                            <i class="bi bi-arrow-right-circle"></i> Confirm & Proceed to Payment
                        </button>
                    </form>
                @else
                    {{-- Not logged in --}}
                    <div class="space-y-4">
                        <div class="bg-[#fff8ee] border border-[#f0d9a0] rounded-xl p-4 text-[0.9rem] text-[#7a5c00] flex items-start gap-3">
                            <i class="bi bi-info-circle-fill text-hotel-gold mt-0.5"></i>
                            <span>You need to be signed in to book a room. Your profile details will be used automatically.</span>
                        </div>
                        <a href="{{ route('guest.login') }}?redirect={{ urlencode(request()->fullUrl()) }}"
                           class="w-full block text-center bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-bold rounded-xl py-3.5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(200,169,110,0.45)]">
                            <i class="bi bi-box-arrow-in-right mr-2"></i> Login to Book This Room
                        </a>
                        <p class="text-center text-gray-500 text-[0.82rem] mt-3">
                            Don't have an account?
                            <a href="{{ route('guest.register') }}" class="text-hotel-gold hover:text-hotel-gold-hover font-semibold transition-colors">Register free</a>
                        </p>
                    </div>
                @endauth

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const pricePerNight = {{ $room->roomType?->price_per_night ?? 0 }};
    const checkInEl   = document.getElementById('check_in_date');
    const checkOutEl  = document.getElementById('check_out_date');
    const nightEl     = document.getElementById('nightCount');
    const totalEl     = document.getElementById('totalPrice');
    const khrEl       = document.getElementById('khrPrice');
    const depositRow  = document.getElementById('depositRow');
    const balanceRow  = document.getElementById('balanceRow');
    const depositEl   = document.getElementById('depositAmount');
    const balanceEl   = document.getElementById('balanceAmount');

    function getSelectedTier() {
        const checked = document.querySelector('input.tier-radio:checked');
        return checked ? parseInt(checked.value, 10) : 100;
    }

    function calculatePrice() {
        if (!checkInEl || !checkOutEl) return;
        const ci = new Date(checkInEl.value);
        const co = new Date(checkOutEl.value);
        if (checkInEl.value && checkOutEl.value && co > ci) {
            const nights = Math.round((co - ci) / (1000 * 60 * 60 * 24));
            const total  = nights * pricePerNight;
            const khrTotal = Math.round(total * 4100);
            const tier   = getSelectedTier();
            const deposit = Math.round(total * tier) / 100;
            const balance = total - deposit;

            nightEl.textContent  = nights + (nights === 1 ? ' night' : ' nights');
            totalEl.textContent  = '$' + total.toFixed(2);
            if (khrEl) khrEl.textContent = '៛ ' + khrTotal.toLocaleString();

            if (tier < 100) {
                depositEl.textContent = '$' + deposit.toFixed(2);
                balanceEl.textContent = '$' + balance.toFixed(2);
                depositRow.style.removeProperty('display');
                balanceRow.style.removeProperty('display');
            } else {
                depositRow.style.setProperty('display', 'none', 'important');
                balanceRow.style.setProperty('display', 'none', 'important');
            }
        } else {
            nightEl.textContent = '—';
            totalEl.textContent = '—';
            if (khrEl) khrEl.textContent = '—';
            depositRow.style.setProperty('display', 'none', 'important');
            balanceRow.style.setProperty('display', 'none', 'important');
        }
    }

    if (checkInEl) {
        checkInEl.addEventListener('change', function () {
            const minOut = new Date(this.value);
            minOut.setDate(minOut.getDate() + 1);
            checkOutEl.min = this.value;
            // Enforce 7-night maximum
            const maxOut = new Date(this.value);
            maxOut.setDate(maxOut.getDate() + 7);
            checkOutEl.max = maxOut.toISOString().split('T')[0];
            if (!checkOutEl.value || new Date(checkOutEl.value) <= new Date(this.value)) {
                checkOutEl.value = minOut.toISOString().split('T')[0];
            } else if (new Date(checkOutEl.value) > maxOut) {
                checkOutEl.value = maxOut.toISOString().split('T')[0];
            }
            calculatePrice();
        });
        checkOutEl.addEventListener('change', calculatePrice);

        // Recalculate whenever a tier radio is changed.
        document.querySelectorAll('input.tier-radio').forEach(function (radio) {
            radio.addEventListener('change', calculatePrice);
        });

        // ── PREFERENCE AVAILABILITY CHECK ─────────────────────────────────────
        const bedSelect = document.getElementById('bed_type');
        const floorSelect = document.getElementById('floor_preference');
        const viewSelect = document.getElementById('view_preference');
        const warningBox = document.getElementById('preferenceWarning');
        const fullyBookedBox = document.getElementById('fullyBookedWarning');
        const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
        const roomId = '{{ $room->id }}';

        async function checkPreferences() {
            if (!checkInEl.value || !checkOutEl.value) return;
            
            const hasPref = (bedSelect && bedSelect.value) || (floorSelect && floorSelect.value) || (viewSelect && viewSelect.value);

            const tierChecked = document.querySelector('input[name="payment_tier"]:checked');
            const tierValue = tierChecked ? tierChecked.value : 100;

            try {
                const response = await fetch(`/rooms/${roomId}/check-preferences`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        check_in_date: checkInEl.value,
                        check_out_date: checkOutEl.value,
                        payment_tier: tierValue,
                        bed_type: bedSelect ? bedSelect.value : null,
                        floor_preference: floorSelect ? floorSelect.value : null,
                        view_preference: viewSelect ? viewSelect.value : null
                    })
                });

                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                
                if (data.available === false && data.reason === 'fully_booked') {
                    if (fullyBookedBox) fullyBookedBox.classList.remove('hidden');
                    if (warningBox) warningBox.classList.add('hidden');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                } else if (data.available === false && data.reason === 'preferences_unavailable' && hasPref) {
                    if (fullyBookedBox) fullyBookedBox.classList.add('hidden');
                    if (warningBox) warningBox.classList.remove('hidden');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                } else {
                    if (fullyBookedBox) fullyBookedBox.classList.add('hidden');
                    if (warningBox) warningBox.classList.add('hidden');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
            } catch (error) {
                console.error('Error checking preferences:', error);
                // On error, we just hide the warning to not block the user
                if (fullyBookedBox) fullyBookedBox.classList.add('hidden');
                if (warningBox) warningBox.classList.add('hidden');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        // Attach listeners to dates, tiers, and preferences
        checkInEl.addEventListener('change', checkPreferences);
        checkOutEl.addEventListener('change', checkPreferences);
        if (bedSelect) bedSelect.addEventListener('change', checkPreferences);
        if (floorSelect) floorSelect.addEventListener('change', checkPreferences);
        if (viewSelect) viewSelect.addEventListener('change', checkPreferences);
        document.querySelectorAll('input.tier-radio').forEach(function (radio) {
            radio.addEventListener('change', checkPreferences);
        });

        calculatePrice();
        checkPreferences();
    }
</script>
@endpush
