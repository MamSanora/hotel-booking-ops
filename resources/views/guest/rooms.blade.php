@extends('layouts.public')

@section('title', 'Our Rooms')
@section('meta_description', 'Browse all available rooms at Dara Meas Hotel — Standard Room, Deluxe Room, and Family Triple Room.')

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-14 lg:py-20 overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center opacity-[0.08]"></div>

    <div class="container mx-auto px-4 relative z-10">
        <h1 class="font-playfair text-4xl lg:text-5xl font-bold text-white mb-4">
            <i class="bi bi-door-open mr-3 text-hotel-gold"></i>Our Rooms
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="flex space-x-2 text-sm text-white/60">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a></li>
                <li class="text-white/30">/</li>
                <li class="text-hotel-gold" aria-current="page">Rooms</li>
            </ol>
        </nav>
    </div>
</div>

{{-- ==========================================
     STICKY SEARCH / FILTER BAR
     ========================================== --}}
<div class="sticky top-[80px] z-30 container mx-auto px-4 mb-8">
    <form method="GET" action="{{ route('rooms.index') }}" class="bg-white/95 backdrop-blur-md shadow-xl border border-gray-100 rounded-2xl p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 items-end">

        <div>
            <label class="block font-semibold text-[0.7rem] uppercase text-gray-500 tracking-wider mb-1.5">Check-In</label>
            <input type="date" name="checkin"
                   min="{{ date('Y-m-d') }}"
                   value="{{ old('checkin', $checkinDate ?? request('checkin')) }}"
                   class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
        </div>

        <div>
            <label class="block font-semibold text-[0.7rem] uppercase text-gray-500 tracking-wider mb-1.5">Check-Out</label>
            <input type="date" name="checkout"
                   value="{{ old('checkout', $checkoutDate ?? request('checkout')) }}"
                   class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
        </div>

        {{-- Guests counter --}}
        <div>
            <label class="block font-semibold text-[0.7rem] uppercase text-gray-500 tracking-wider mb-1.5">Guests</label>
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 flex items-center justify-between">
                    <div>
                        <div class="text-[0.62rem] text-gray-500 font-semibold uppercase tracking-wider">Adults</div>
                        <div class="text-gray-800 text-sm font-bold" id="adults-display-rooms">{{ $adults ?? request('adults', 1) }}</div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="adjustRoomCount('adults', -1)"
                                class="w-6 h-6 rounded-full bg-gray-200 hover:bg-hotel-gold hover:text-white text-gray-700 font-bold text-sm flex items-center justify-center transition-colors">&minus;</button>
                        <button type="button" onclick="adjustRoomCount('adults', 1)"
                                class="w-6 h-6 rounded-full bg-gray-200 hover:bg-hotel-gold hover:text-white text-gray-700 font-bold text-sm flex items-center justify-center transition-colors">+</button>
                    </div>
                    <input type="hidden" name="adults" id="adults-value-rooms" value="{{ $adults ?? request('adults', 1) }}">
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5 flex items-center justify-between">
                    <div>
                        <div class="text-[0.62rem] text-gray-500 font-semibold uppercase tracking-wider">Children</div>
                        <div class="text-gray-800 text-sm font-bold" id="children-display-rooms">{{ $children ?? request('children', 0) }}</div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="adjustRoomCount('children', -1)"
                                class="w-6 h-6 rounded-full bg-gray-200 hover:bg-hotel-gold hover:text-white text-gray-700 font-bold text-sm flex items-center justify-center transition-colors">&minus;</button>
                        <button type="button" onclick="adjustRoomCount('children', 1)"
                                class="w-6 h-6 rounded-full bg-gray-200 hover:bg-hotel-gold hover:text-white text-gray-700 font-bold text-sm flex items-center justify-center transition-colors">+</button>
                    </div>
                    <input type="hidden" name="children" id="children-value-rooms" value="{{ $children ?? request('children', 0) }}">
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="w-full bg-hotel-dark hover:bg-hotel-accent text-white font-semibold rounded-lg px-4 py-2 transition-colors duration-200 text-sm">
                <i class="bi bi-search mr-2"></i>Search Rooms
            </button>
        </div>
    </form>
</div>


<div class="container mx-auto px-4 pb-12"
     x-data="roomListingApp()"
     x-init="init()">

    {{-- ==========================================
         TYPE FILTER PILLS
         ========================================== --}}
    <div class="flex flex-wrap gap-2 mb-6 mt-4">
        <a href="{{ route('rooms.index', request()->except('type')) }}"
           class="border-[1.5px] rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200
                  {{ !request('type') ? 'bg-hotel-dark border-hotel-dark text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-hotel-dark hover:border-hotel-dark hover:text-white' }}">
            All Types ({{ $roomTypes->count() }})
        </a>
        @foreach($roomTypes as $rt)
            <a href="{{ route('rooms.index', array_merge(request()->all(), ['type' => $rt->slug])) }}"
               class="border-[1.5px] rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200
                      {{ request('type') === $rt->slug ? 'bg-hotel-dark border-hotel-dark text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-hotel-dark hover:border-hotel-dark hover:text-white' }}">
                {{ $rt->display_name }}
            </a>
        @endforeach
    </div>

    {{-- Results count + capacity hint --}}
    <div class="flex items-center justify-between mb-8">
        <p class="text-gray-500 text-sm">
            <i class="bi bi-grid mr-1"></i>
            Showing <strong class="text-gray-800">{{ $roomTypes->count() }}</strong> room type(s)
            @if(request('checkin') && request('checkout'))
                available between
                <strong class="text-gray-800">{{ \Carbon\Carbon::parse(request('checkin'))->format('M d, Y') }}</strong> and
                <strong class="text-gray-800">{{ \Carbon\Carbon::parse(request('checkout'))->format('M d, Y') }}</strong>
            @endif
        </p>
        {{-- Live guest count display --}}
        <p class="text-gray-400 text-xs" x-show="totalGuests > 1">
            <i class="bi bi-people mr-1"></i>
            Showing rooms for <span class="font-semibold text-gray-600" x-text="adults"></span> Adult(s), <span class="font-semibold text-gray-600" x-text="children"></span> Child(ren)
        </p>
    </div>

    {{-- ==========================================
         CAMBODIAN TRUST & PAYMENT SECURITY BAR (POINT 4)
         ========================================== --}}
    <div class="bg-gradient-to-r from-hotel-dark via-[#24243e] to-hotel-dark rounded-2xl p-5 md:p-6 text-white shadow-[0_8px_25px_rgba(26,26,46,0.15)] border border-white/10 mb-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 items-center divide-y sm:divide-y-0 sm:divide-x divide-white/10 text-center sm:text-left">
            
            <div class="flex items-center justify-center sm:justify-start gap-3 pt-2 sm:pt-0">
                <div class="w-10 h-10 rounded-xl bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                    <i class="bi bi-qr-code"></i>
                </div>
                <div>
                    <h5 class="font-bold text-white text-xs">KHQR &amp; ABA Pay</h5>
                    <p class="text-white/60 text-[0.72rem] mt-0.5">Instant Bakong scan &amp; pay accepted.</p>
                </div>
            </div>

            <div class="flex items-center justify-center sm:justify-start gap-3 pt-2 sm:pt-0 sm:pl-5">
                <div class="w-10 h-10 rounded-xl bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h5 class="font-bold text-white text-xs">No Hidden Fees</h5>
                    <p class="text-white/60 text-[0.72rem] mt-0.5">All room taxes & amenities included.</p>
                </div>
            </div>

            <div class="flex items-center justify-center sm:justify-start gap-3 pt-2 sm:pt-0 lg:pl-5">
                <div class="w-10 h-10 rounded-xl bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                    <i class="bi bi-calendar2-check-fill"></i>
                </div>
                <div>
                    <h5 class="font-bold text-white text-xs">Instant Confirmation</h5>
                    <p class="text-white/60 text-[0.72rem] mt-0.5">Direct SMS & email receipt.</p>
                </div>
            </div>

            <div class="flex items-center justify-center sm:justify-start gap-3 pt-2 sm:pt-0 lg:pl-5">
                <div class="w-10 h-10 rounded-xl bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold flex items-center justify-center text-lg flex-shrink-0 shadow-sm">
                    <i class="bi bi-award"></i>
                </div>
                <div>
                    <h5 class="font-bold text-white text-xs">Best Rate Guarantee</h5>
                    <p class="text-white/60 text-[0.72rem] mt-0.5">Direct rates starting at $30/night.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- ==========================================
         ROOM GRID
         ========================================== --}}
    @php
        // Image sliders per room type.
        // Priority: RoomType->images (DB JSON) first, then fallback to blade array.
        $roomSliderImagesFallback = [
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
        $fallbackImg = asset('room/Standard Double 1.jpg');
    @endphp

    @if($roomTypes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($roomTypes as $roomType)
                @php
                    // Use DB images if set, otherwise fall back to blade array
                    $imgs = (is_array($roomType->images) && count($roomType->images) > 0)
                        ? array_map(fn($p) => asset('room/' . $p), $roomType->images)
                        : ($roomSliderImagesFallback[$roomType->slug] ?? []);
                    $firstImg     = $imgs[0] ?? $fallbackImg;
                    $isAvailable  = $availability[$roomType->id] ?? true;
                    $roomsLeft    = $availableCounts[$roomType->id] ?? $roomType->getAvailableCount($checkinDate, $checkoutDate);
                    $sliderId     = 'slider-' . $roomType->slug;
                @endphp

                {{-- Each card is its own Alpine scope that reads from the parent roomListingApp() data --}}
                <div class="group bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.07)] hover:shadow-[0_12px_35px_rgba(0,0,0,0.13)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col relative"
                     x-data="{ slug: '{{ $roomType->slug }}' }">

                    {{-- Image Slider --}}
                    @if(count($imgs) > 0)
                        <div class="relative overflow-hidden h-[220px]" x-data="{ current: 0, imgs: {{ json_encode($imgs) }} }" x-init="setInterval(() => { current = (current + 1) % imgs.length }, 3500)">
                            <template x-for="(img, i) in imgs" :key="i">
                                <img :src="img" :alt="'{{ $roomType->display_name }} image ' + (i+1)"
                                     class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500"
                                     :class="i === current ? 'opacity-100 group-hover:scale-105 transition-transform duration-500' : 'opacity-0'">
                            </template>
                            {{-- Prev/Next Arrows --}}
                            @if(count($imgs) > 1)
                            <button type="button" @click.prevent="current = (current - 1 + imgs.length) % imgs.length"
                                    class="absolute left-2 top-1/2 -translate-y-1/2 z-20 w-7 h-7 flex items-center justify-center bg-black/40 hover:bg-black/60 text-white rounded-full text-xs transition opacity-0 group-hover:opacity-100">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" @click.prevent="current = (current + 1) % imgs.length"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 z-20 w-7 h-7 flex items-center justify-center bg-black/40 hover:bg-black/60 text-white rounded-full text-xs transition opacity-0 group-hover:opacity-100">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                            {{-- Dot Indicators --}}
                            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1 z-20 opacity-0 group-hover:opacity-100 transition">
                                <template x-for="(img, i) in imgs" :key="i">
                                    <button type="button" @click.prevent="current = i"
                                            :class="i === current ? 'bg-white w-4' : 'bg-white/50 w-2'"
                                            class="h-2 rounded-full transition-all duration-300"></button>
                                </template>
                            </div>
                            @endif

                            {{-- Availability badge: reacts to both server-side and Alpine.js state --}}
                            @if(!$isAvailable)
                                <span class="absolute top-4 right-4 z-10 bg-red-500/90 backdrop-blur-sm text-white text-[0.72rem] font-bold px-3.5 py-1.5 rounded-full tracking-wider shadow-sm flex items-center gap-1.5">
                                    <i class="bi bi-x-circle-fill"></i>Fully Booked
                                </span>
                            @endif
                        </div>
                    @else
                        {{-- No images: show placeholder --}}
                        <div class="relative overflow-hidden h-[220px] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <div class="text-center text-gray-400">
                                <i class="bi bi-door-closed text-5xl block mb-2"></i>
                                <span class="text-xs font-medium">Test Room</span>
                            </div>
                        </div>
                    @endif

                    <div class="p-6 flex flex-col flex-grow">
                        {{-- Type Name & Price --}}
                        <div class="flex justify-between items-center mb-3">
                            <span class="bg-gray-50 text-gray-700 border border-gray-200 text-[0.75rem] px-2.5 py-1 rounded font-medium">
                                <i class="bi bi-people mr-1"></i>Up to {{ $roomType->adult_capacity }} Adults, {{ $roomType->child_capacity }} Children
                            </span>
                            <div class="font-playfair text-2xl font-bold text-hotel-gold">
                                <span data-price-usd="{{ $roomType->price_per_night ?? 0 }}">${{ number_format($roomType->price_per_night ?? 0, 0) }}</span><span class="text-[0.8rem] text-gray-400 font-sans font-normal" data-night-label data-night-label-km="/យប់">/night</span>
                            </div>
                        </div>

                        <h5 class="font-bold text-xl text-hotel-dark mb-1">{{ $roomType->display_name }}</h5>
                        @if($roomType->size_sqm)
                        <p class="text-[0.78rem] text-gray-400 mb-2"><i class="bi bi-aspect-ratio mr-1"></i>{{ $roomType->size_sqm }} m&sup2;</p>
                        @endif
                        <p class="text-gray-500 text-[0.88rem] leading-[1.6] mb-4 flex-grow">
                            {{ Str::limit($roomType->description ?? 'A comfortable and well-appointed room at Dara Meas Hotel, Phnom Penh.', 90) }}
                        </p>

                        {{-- Amenity Tags --}}
                        <div class="flex flex-wrap gap-2 mb-6">
                            <span class="bg-gray-50 text-gray-700 border border-gray-200 text-[0.72rem] px-2.5 py-1 rounded">
                                <i class="bi bi-wifi mr-1"></i>Wi-Fi
                            </span>
                            <span class="bg-gray-50 text-gray-700 border border-gray-200 text-[0.72rem] px-2.5 py-1 rounded">
                                <i class="bi bi-snow mr-1"></i>A/C
                            </span>
                            <span class="bg-gray-50 text-gray-700 border border-gray-200 text-[0.72rem] px-2.5 py-1 rounded">
                                <i class="bi bi-tv mr-1"></i>TV
                            </span>
                        </div>

                        {{-- Action Button: auto-calculates number of rooms needed from guest count --}}
                        <div>
                            @if($isAvailable)
                                <button type="button"
                                        @click="navigateToRoom('{{ $roomType->slug }}', {{ $roomType->adult_capacity }}, {{ $roomType->child_capacity }}, '{{ $checkinDate }}', '{{ $checkoutDate }}')"
                                        class="w-full block text-center bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-semibold py-2.5 rounded-lg transition-all duration-300 hover:-translate-y-[1px] hover:shadow-[0_4px_15px_rgba(200,169,110,0.4)]">
                                    <i class="bi bi-calendar-plus mr-1"></i>Book Now
                                </button>
                            @else
                                <span class="w-full block text-center bg-gray-50 text-gray-400 border border-gray-200 font-semibold text-[0.9rem] py-2.5 rounded-lg cursor-not-allowed">
                                    Fully Booked
                                </span>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-hotel-light rounded-2xl text-center py-16 px-4">
            <i class="bi bi-calendar-x text-[3.5rem] text-hotel-gold mb-4 inline-block"></i>
            <h4 class="font-bold text-2xl text-hotel-dark mb-3">No Room Types Found</h4>
            <p class="text-gray-500 mb-6">
                No room types match your filters.<br>
                Try different dates or remove filters.
            </p>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-hotel-dark hover:bg-hotel-accent text-white font-semibold px-6 py-2.5 rounded-lg transition-colors duration-200">
                <i class="bi bi-arrow-left mr-2"></i>Clear Filters
            </a>
        </div>
    @endif

</div>

{{-- ==========================================
     MULTI-TYPE BOOKING SECTION
     Guests can specify number of rooms per type.
     Single type → room-detail page.
     Multiple types → multi-room checkout.
     ========================================== --}}
<div class="bg-gradient-to-br from-hotel-dark to-[#24243e] py-16" id="multi-type-booking"
     x-data="multiTypeBooking()"
     x-init="init()">
    <div class="container mx-auto px-4">
        <div class="text-center mb-10">
            <span class="inline-block bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest mb-4">Multi-Type Booking</span>
            <h2 class="font-playfair text-3xl lg:text-4xl font-bold text-white mb-3">Need More Than One Room?</h2>
            <p class="text-white/60 text-sm max-w-xl mx-auto">Enter the number of rooms you need for each type below. Click <strong class="text-hotel-gold">Book Now</strong> to proceed — we'll route you to the right checkout automatically.</p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-2xl overflow-hidden max-w-3xl mx-auto">

            {{-- One row per visible room type (excluding test rooms) --}}
            @foreach($roomTypes->where('slug', '!=', 'test_room') as $rt)
            @php
                $imgs2 = (is_array($rt->images) && count($rt->images) > 0)
                    ? array_map(fn($p) => asset('room/' . $p), $rt->images)
                    : ($roomSliderImagesFallback[$rt->slug] ?? []);
                $thumb2  = $imgs2[0] ?? asset('room/Standard Double 1.jpg');
                $avail2  = $availability[$rt->id] ?? false;
                $maxQty2 = $availableCounts[$rt->id] ?? 0;
            @endphp
            <div class="flex items-center gap-4 px-5 py-4 border-b border-white/10 last:border-b-0">

                {{-- Thumbnail --}}
                <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0 bg-white/10">
                    <img src="{{ $thumb2 }}" alt="{{ $rt->display_name }}" class="w-full h-full object-cover">
                </div>

                {{-- Info --}}
                <div class="flex-grow min-w-0">
                    <div class="font-bold text-white text-sm">{{ $rt->display_name }}</div>
                    <div class="text-white/50 text-xs mt-0.5">
                        <span>Up to {{ $rt->adult_capacity }} Adults, {{ $rt->child_capacity }} Children/room</span>
                        <span class="mx-1.5">&middot;</span>
                        <span class="text-hotel-gold font-semibold">${{ number_format($rt->price_per_night, 0) }}/night</span>
                    </div>
                    @if($avail2 && $maxQty2 > 0)
                        <div class="text-emerald-400 text-[0.68rem] mt-0.5 font-medium">
                            <i class="bi bi-check-circle-fill mr-1"></i>{{ $maxQty2 }} available
                        </div>
                    @else
                        <div class="text-red-400 text-[0.68rem] mt-0.5 font-medium">
                            <i class="bi bi-x-circle-fill mr-1"></i>Fully booked for selected dates
                        </div>
                    @endif
                </div>

                {{-- Number of rooms input --}}
                <div class="flex-shrink-0 flex flex-col items-end gap-1">
                    <label class="text-white/40 text-[0.65rem] uppercase tracking-wider">No. of Rooms</label>
                    <input type="number"
                           min="0"
                           max="{{ $maxQty2 }}"
                           value="0"
                           {{ !($avail2 && $maxQty2 > 0) ? 'disabled' : '' }}
                           @input="setQty('{{ $rt->slug }}', $event.target.value, {{ $maxQty2 }}, {{ (float)$rt->price_per_night }})"
                           :class="errors['{{ $rt->slug }}'] ? 'border-red-500' : 'border-white/20'"
                           id="pkg-input-{{ $rt->slug }}"
                           data-price="{{ (float)$rt->price_per_night }}"
                           class="w-20 text-center bg-white/10 disabled:bg-white/5 border rounded-lg px-2 py-1.5 text-white font-bold text-base focus:outline-none focus:ring-2 focus:ring-hotel-gold/50 disabled:text-white/30 disabled:cursor-not-allowed">
                    <span x-show="errors['{{ $rt->slug }}']" x-cloak
                          class="text-red-400 text-[0.65rem]"
                          x-text="errors['{{ $rt->slug }}']"></span>
                </div>

            </div>
            @endforeach

            {{-- Summary footer --}}
            <div class="px-5 py-4 bg-white/5 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-white/10">
                <div class="text-white/70 text-sm">
                    <template x-if="totalRooms === 0">
                        <span>Enter the number of rooms above to get started.</span>
                    </template>
                    <template x-if="totalRooms > 0">
                        <span>
                            <span class="font-bold text-white" x-text="totalRooms"></span> room(s)
                            &middot; <span class="text-hotel-gold font-semibold" x-text="'$' + totalPrice.toFixed(0) + '/night'"></span>
                        </span>
                    </template>
                </div>
                <button type="button"
                        @click="proceedToBooking('{{ $checkinDate }}', '{{ $checkoutDate }}')"
                        :disabled="totalRooms <= 0"
                        class="inline-flex items-center gap-2 bg-hotel-gold hover:bg-[#b8935a] disabled:bg-gray-600 disabled:cursor-not-allowed text-white font-semibold px-6 py-2.5 rounded-xl transition-all duration-200 text-sm shadow-lg">
                    <i class="bi bi-calendar-plus"></i>
                    <span x-text="totalTypes === 1 ? 'Book This Room Type' : 'Book Multiple Room Types'"></span>
                </button>
            </div>
        </div>

        <p class="text-center text-white/30 text-xs mt-6">Prices shown are per night. Total will be calculated at checkout based on your selected dates.</p>
    </div>
</div>

@endsection



@push('scripts')
<script>
    // ── Date constraint on sticky search bar ─────────────────────────────────
    const checkinEl  = document.querySelector('input[name="checkin"]');
    const checkoutEl = document.querySelector('input[name="checkout"]');
    if (checkinEl && checkoutEl) {
        checkinEl.addEventListener('change', function () {
            checkoutEl.min = this.value;
            if (checkoutEl.value && checkoutEl.value <= this.value) {
                const d = new Date(this.value);
                d.setDate(d.getDate() + 1);
                checkoutEl.value = d.toISOString().split('T')[0];
            }
        });
    }

    // ── Guest counter buttons on sticky search bar ────────────────────────────
    function adjustRoomCount(field, delta) {
        const valEl  = document.getElementById(field + '-value-rooms');
        const dispEl = document.getElementById(field + '-display-rooms');
        if (!valEl || !dispEl) return;
        const min = field === 'adults' ? 1 : 0;
        const max = 20;
        let cur = parseInt(valEl.value, 10);
        cur = Math.min(max, Math.max(min, cur + delta));
        valEl.value        = cur;
        dispEl.textContent = cur;
        // Notify the Alpine roomListingApp so auto-calc stays in sync
        window.dispatchEvent(new CustomEvent('guestCountChanged', {
            detail: {
                adults:   parseInt(document.getElementById('adults-value-rooms')?.value  || 1,  10),
                children: parseInt(document.getElementById('children-value-rooms')?.value || 0, 10),
            }
        }));
    }

    // ── Room Listing App (Alpine.js parent component) ─────────────────────────
    // Tracks guest count so the room-grid cards can auto-calc rooms on click.
    function roomListingApp() {
        return {
            adults:       {{ $adults ?? 1 }},
            children:     {{ $children ?? 0 }},
            capacityData: @json($capacityData ?? []),

            get totalGuests() {
                return this.adults + this.children;
            },

            init() {
                window.addEventListener('guestCountChanged', (e) => {
                    this.adults   = e.detail.adults;
                    this.children = e.detail.children;
                });
            },

            // Auto-calculate how many rooms of this type are needed for current guests.
            autoCalcRooms(adultCap, childCap) {
                // Ensure capacities are at least 1 for division to avoid Infinity
                const safeAdultCap = Math.max(1, adultCap);
                const safeChildCap = childCap > 0 ? childCap : 1; 
                
                // We need enough rooms to fit all adults AND all children
                const roomsForAdults = Math.ceil(this.adults / safeAdultCap);
                const roomsForChildren = this.children > 0 ? Math.ceil(this.children / safeChildCap) : 0;
                
                return Math.max(1, roomsForAdults, roomsForChildren);
            },

            // Navigate to room-detail with auto-calculated rooms pre-filled.
            navigateToRoom(slug, adultCap, childCap, checkin, checkout) {
                const rooms = this.autoCalcRooms(adultCap, childCap);
                let url = `/rooms/${slug}?rooms=${rooms}`;
                if (checkin)  url += `&checkin=${checkin}`;
                if (checkout) url += `&checkout=${checkout}`;
                window.location.href = url;
            },
        };
    }

    // ── Multi-Type Booking (Alpine.js component) ──────────────────────────────
    // Handles the number-input section below the room grid.
    function multiTypeBooking() {
        return {
            quantities: {},   // slug → qty
            prices:     {},   // slug → price per night
            errors:     {},   // slug → error string

            get totalRooms() {
                return Object.values(this.quantities).reduce((s, v) => s + (v || 0), 0);
            },

            // How many room TYPES have at least 1 room selected
            get totalTypes() {
                return Object.values(this.quantities).filter(v => v > 0).length;
            },

            get totalPrice() {
                return Object.entries(this.quantities).reduce((s, [slug, qty]) => {
                    return s + ((this.prices[slug] || 0) * (qty || 0));
                }, 0);
            },

            init() {
                // Seed quantities/prices from DOM inputs (in case browser restores values on back/refresh)
                document.querySelectorAll('[id^="pkg-input-"]').forEach(el => {
                    const slug = el.id.replace('pkg-input-', '');
                    this.quantities[slug] = parseInt(el.value, 10) || 0;
                    this.prices[slug] = parseFloat(el.getAttribute('data-price')) || 0;
                });
            },

            setQty(slug, rawVal, max, price) {
                let val = parseInt(rawVal, 10) || 0;
                val = Math.max(0, Math.min(max, val));

                // Sync input value back in case browser didn't clamp
                const el = document.getElementById('pkg-input-' + slug);
                if (el) el.value = val;

                this.prices[slug]    = price;
                this.quantities[slug] = val;

                if (val > max && max > 0) {
                    this.errors[slug] = `Max ${max} available`;
                } else {
                    this.errors[slug] = null;
                }
            },

            proceedToBooking(checkin, checkout) {
                const selected = Object.entries(this.quantities).filter(([, qty]) => qty > 0);
                if (selected.length === 0) return;

                const dateQs = (checkin && checkout) ? `checkin=${checkin}&checkout=${checkout}` : '';

                // Single room type selected — go to normal room-detail page
                if (selected.length === 1) {
                    const [slug, qty] = selected[0];
                    let url = `/rooms/${slug}?rooms=${qty}`;
                    if (dateQs) url += `&${dateQs}`;
                    window.location.href = url;
                    return;
                }

                // Multiple room types — build cart and go to multi-type checkout
                const cart = selected.map(([slug, qty]) => ({ slug, qty }));
                const cartParam = encodeURIComponent(JSON.stringify(cart));
                let url = `/rooms/checkout/multi?cart=${cartParam}`;
                if (dateQs) url += `&${dateQs}`;
                window.location.href = url;
            },
        };
    }
</script>
@endpush
