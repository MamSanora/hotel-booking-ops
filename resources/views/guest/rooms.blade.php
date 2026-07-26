@extends('layouts.public')

@section('title', 'Our Rooms')
@section('meta_description', 'Browse all available rooms at Dara Meas Hotel — Standard Room, Deluxe Room, and Family Triple Room.')

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-14 lg:py-20 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>

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

<div class="container mx-auto px-4 py-12">

    {{-- ==========================================
         SEARCH / FILTER BAR
         ========================================== --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.08)] p-6 lg:p-8 mb-8">
        <form method="GET" action="{{ route('rooms.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

            <div>
                <label class="block font-semibold text-[0.75rem] uppercase text-gray-500 tracking-wider mb-2">Check-In</label>
                <input type="date" name="checkin"
                       min="{{ date('Y-m-d') }}"
                       value="{{ old('checkin', $checkinDate ?? request('checkin')) }}"
                       class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none">
            </div>

            <div>
                <label class="block font-semibold text-[0.75rem] uppercase text-gray-500 tracking-wider mb-2">Check-Out</label>
                <input type="date" name="checkout"
                       value="{{ old('checkout', $checkoutDate ?? request('checkout')) }}"
                       class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none">
            </div>

            {{-- Adults / Children Counter --}}
            <div>
                <label class="block font-semibold text-[0.75rem] uppercase text-gray-500 tracking-wider mb-2">Guests</label>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 flex items-center justify-between">
                        <div>
                            <div class="text-[0.65rem] text-gray-500 font-semibold uppercase tracking-wider">Adults</div>
                            <div class="text-gray-800 text-sm font-bold" id="adults-display-rooms">{{ request('adults', 1) }}</div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="adjustRoomCount('adults', -1)"
                                    class="w-6 h-6 rounded-full bg-gray-200 hover:bg-hotel-gold hover:text-white text-gray-700 font-bold text-sm flex items-center justify-center transition-colors">−</button>
                            <button type="button" onclick="adjustRoomCount('adults', 1)"
                                    class="w-6 h-6 rounded-full bg-gray-200 hover:bg-hotel-gold hover:text-white text-gray-700 font-bold text-sm flex items-center justify-center transition-colors">+</button>
                        </div>
                        <input type="hidden" name="adults" id="adults-value-rooms" value="{{ request('adults', 1) }}">
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 flex items-center justify-between">
                        <div>
                            <div class="text-[0.65rem] text-gray-500 font-semibold uppercase tracking-wider">Children</div>
                            <div class="text-gray-800 text-sm font-bold" id="children-display-rooms">{{ request('children', 0) }}</div>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="adjustRoomCount('children', -1)"
                                    class="w-6 h-6 rounded-full bg-gray-200 hover:bg-hotel-gold hover:text-white text-gray-700 font-bold text-sm flex items-center justify-center transition-colors">−</button>
                            <button type="button" onclick="adjustRoomCount('children', 1)"
                                    class="w-6 h-6 rounded-full bg-gray-200 hover:bg-hotel-gold hover:text-white text-gray-700 font-bold text-sm flex items-center justify-center transition-colors">+</button>
                        </div>
                        <input type="hidden" name="children" id="children-value-rooms" value="{{ request('children', 0) }}">
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full bg-hotel-dark hover:bg-hotel-accent text-white font-semibold rounded-lg px-4 py-2.5 transition-colors duration-200">
                    <i class="bi bi-search mr-2"></i>Search Rooms
                </button>
            </div>
        </form>
    </div>

    {{-- ==========================================
         TYPE FILTER PILLS
         ========================================== --}}
    <div class="flex flex-wrap gap-2 mb-6">
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

    {{-- Results count --}}
    <p class="text-gray-500 text-sm mb-8">
        <i class="bi bi-grid mr-1"></i>
        Showing <strong class="text-gray-800">{{ $roomTypes->count() }}</strong> room type(s)
        @if(request('checkin') && request('checkout'))
            available between
            <strong class="text-gray-800">{{ \Carbon\Carbon::parse(request('checkin'))->format('M d, Y') }}</strong> and
            <strong class="text-gray-800">{{ \Carbon\Carbon::parse(request('checkout'))->format('M d, Y') }}</strong>
        @endif
    </p>

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
        // Image sliders per room type — using actual files from public/room/
        // Rules:
        //   - Balcony 1.webp       → shared by Standard, Deluxe, Family Triple
        //   - Standard Bathroom *  → Standard Room only
        //   - Deluxe Bathroom *    → Deluxe Room AND Family Triple Room
        //   - Standard Double/Twin → Standard Room bedroom images
        //   - Deluxe Double/Twin   → Deluxe Room bedroom images
        //   - Family Triple Room * → Family Triple Room bedroom images
        $roomSliderImages = [
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
            'test_room' => [], // No images for Test Room
        ];
        $fallbackImg = asset('room/Standard Double 1.jpg');
    @endphp

    @if($roomTypes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($roomTypes as $roomType)
                @php
                    $imgs = $roomSliderImages[$roomType->slug] ?? [];
                    $firstImg = $imgs[0] ?? $fallbackImg;
                    $isAvailable = $availability[$roomType->id] ?? true;
                    // Pick a representative physical room for the detail-page link.
                    $representativeRoom = $roomType->rooms()->where('current_status', '!=', 'maintenance')->first();
                    $roomsLeft = $availableCounts[$roomType->id] ?? $roomType->getAvailableCount($checkinDate, $checkoutDate);
                    $sliderId = 'slider-' . $roomType->slug;
                @endphp
                <div class="group bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.07)] hover:shadow-[0_12px_35px_rgba(0,0,0,0.13)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col relative">

                    {{-- Image Slider --}}
                    @if(count($imgs) > 0)
                        <div class="relative overflow-hidden h-[220px]" x-data="{ current: 0, imgs: {{ json_encode($imgs) }} }">
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
                            @if(!($isAvailable && $roomsLeft > 0))
                                <span class="absolute top-4 right-4 z-10 bg-red-500/90 backdrop-blur-sm text-white text-[0.72rem] font-bold px-3.5 py-1.5 rounded-full tracking-wider shadow-sm flex items-center gap-1.5">
                                    <i class="bi bi-x-circle-fill"></i>Fully Booked
                                </span>
                            @endif
                        </div>
                    @else
                        {{-- Test Room: no images, show placeholder --}}
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
                                <i class="bi bi-people mr-1"></i>Up to {{ $roomType->capacity }} guests
                            </span>
                            <div class="font-playfair text-2xl font-bold text-hotel-gold">
                                <span data-price-usd="{{ $roomType->price_per_night ?? 0 }}">${{ number_format($roomType->price_per_night ?? 0, 0) }}</span><span class="text-[0.8rem] text-gray-400 font-sans font-normal" data-night-label data-night-label-km="/យប់">/night</span>
                            </div>
                        </div>

                        <h5 class="font-bold text-xl text-hotel-dark mb-1">{{ $roomType->display_name }}</h5>
                        @if($roomType->size_sqm)
                        <p class="text-[0.78rem] text-gray-400 mb-2"><i class="bi bi-aspect-ratio mr-1"></i>{{ $roomType->size_sqm }} m²</p>
                        @endif
                        <p class="text-gray-500 text-[0.88rem] leading-[1.6] mb-5 flex-grow">
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

                        {{-- Action Button — single Book button (no separate Details) --}}
                        <div>
                            @if($representativeRoom)
                                @if($isAvailable)
                                    <a href="{{ route('rooms.show', $representativeRoom) }}{{ $checkinDate ? '?checkin='.$checkinDate.'&checkout='.$checkoutDate : '' }}"
                                       class="w-full block text-center bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-semibold py-2.5 rounded-lg transition-all duration-300 hover:-translate-y-[1px] hover:shadow-[0_4px_15px_rgba(200,169,110,0.4)]">
                                        <i class="bi bi-calendar-plus mr-1"></i>Book Now
                                    </a>
                                @else
                                    <span class="w-full block text-center bg-gray-50 text-gray-400 border border-gray-200 font-semibold text-[0.9rem] py-2.5 rounded-lg cursor-not-allowed">
                                        Fully Booked
                                    </span>
                                @endif
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

@endsection

@push('scripts')
<script>
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

    function adjustRoomCount(field, delta) {
        const valEl  = document.getElementById(field + '-value-rooms');
        const dispEl = document.getElementById(field + '-display-rooms');
        if (!valEl || !dispEl) return;
        const min = field === 'adults' ? 1 : 0;
        const max = field === 'adults' ? 2 : 2;
        let cur = parseInt(valEl.value, 10);
        cur = Math.min(max, Math.max(min, cur + delta));
        valEl.value    = cur;
        dispEl.textContent = cur;
    }
</script>
@endpush
