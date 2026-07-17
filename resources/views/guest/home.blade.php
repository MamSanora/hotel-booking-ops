@extends('layouts.public')

@section('title', 'Dara Meas Hotel — Phnom Penh, Cambodia')
@section('meta_description', 'Book your stay at Dara Meas Hotel, a comfortable hotel in the heart of Phnom Penh, Cambodia. Standard Twin, Double, and Deluxe rooms from $35/night.')

@section('content')

{{-- ==========================================
    BACKGROUND IMAGE SECTION
     ========================================== --}}
<div class="relative min-h-[85vh] flex items-center overflow-hidden">
    {{-- <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1920&q=80')] bg-cover bg-center bg-no-repeat"></div> --}}
    {{-- <div class="absolute inset-0 bg-[url('{{ asset('images/DaraLobby.jpg') }}')] bg-cover bg-center bg-no-repeat"></div> --}}
    <div class="absolute inset-0 bg-gradient-to-br from-hotel-dark/80 via-hotel-dark/60 to-transparent"></div>

    <div class="relative z-10 container mx-auto px-4 md:px-6 py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- LEFT: Hero text & CTA buttons --}}
            <div>
                <span class="inline-block text-hotel-gold font-semibold uppercase tracking-widest text-xs mb-4 border border-hotel-gold/40 bg-hotel-gold/10 px-4 py-1.5 rounded-full">
                    Phnom Penh, Cambodia
                </span>
                <h1 class="font-playfair text-5xl lg:text-[3.8rem] font-extrabold text-white leading-tight mb-6">
                    Your Home Away<br>From Home.
                </h1>
                <p class="text-white/75 text-lg leading-relaxed mb-10 max-w-lg">
                    Discover the warmth of Cambodian hospitality at Dara Meas Hotel — 47 elegantly appointed rooms, 24/7 service, and memories that last a lifetime.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-hotel-gold hover:bg-[#b8935a] text-hotel-dark font-bold px-8 py-3.5 rounded-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(200,169,110,0.45)] text-[1.05rem]">
                        <i class="bi bi-door-open mr-2"></i> Browse Our Rooms
                    </a>
                    <a href="{{ route('about') }}" class="inline-flex items-center bg-white/10 hover:bg-white/20 text-white font-bold px-8 py-3.5 rounded-xl border border-white/25 hover:border-white/40 transition-all duration-300 text-[1.05rem]">
                        <i class="bi bi-info-circle mr-2"></i> About Us
                    </a>
                </div>
            </div>

            {{-- RIGHT: Check Availability card --}}
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 shadow-[0_8px_40px_rgba(0,0,0,0.25)]">
                <h3 class="font-playfair text-xl font-bold text-white mb-1">
                    <i class="bi bi-calendar2-check text-hotel-gold mr-2"></i>Check Availability
                </h3>
                <p class="text-white/60 text-sm mb-5">Find available rooms for your preferred dates.</p>
                <form action="{{ route('rooms.index') }}" method="GET" class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block font-semibold text-[0.7rem] uppercase text-white/70 tracking-wider mb-1.5">Check-In</label>
                        <input type="date" name="checkin" min="{{ date('Y-m-d') }}"
                               class="w-full border-[1.5px] border-white/20 bg-white/10 text-white rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/30 transition-all outline-none placeholder-white/40 [color-scheme:dark]">
                    </div>
                    <div>
                        <label class="block font-semibold text-[0.7rem] uppercase text-white/70 tracking-wider mb-1.5">Check-Out</label>
                        <input type="date" name="checkout"
                               class="w-full border-[1.5px] border-white/20 bg-white/10 text-white rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/30 transition-all outline-none [color-scheme:dark]">
                    </div>
                    <div>
                        <label class="block font-semibold text-[0.7rem] uppercase text-white/70 tracking-wider mb-1.5">Room Type</label>
                        <select name="type" class="w-full border-[1.5px] border-white/20 bg-hotel-dark/60 text-white rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/30 transition-all outline-none">
                            <option value="">Any Type</option>
                            <option value="standard_twin">Standard Twin</option>
                            <option value="standard_double">Standard Double</option>
                            <option value="deluxe_double">Deluxe Double</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-hotel-gold hover:bg-[#b8935a] text-hotel-dark font-bold rounded-xl px-4 py-3.5 transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-[0_4px_16px_rgba(200,169,110,0.5)] hover:-translate-y-0.5">
                        <i class="bi bi-search"></i> Search Rooms
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- Background image fade --}}
    <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
</div>

{{-- ==========================================
     QUICK STATS
     ========================================== --}}
<div class="container mx-auto px-4 md:px-6 -mt-6 relative z-20 mb-16">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['bi-building', '47', 'Guest Rooms'],
            ['bi-calendar2-check', '2019', 'Est. Year'],
            ['bi-clock', '24/7', 'Front Desk'],
            ['bi-geo-alt', 'Phnom Penh', 'Prime Location'],
        ] as [$icon, $value, $label])
        <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.09)] p-5 text-center border border-[#f0ebe2]">
            <i class="bi {{ $icon }} text-hotel-gold text-2xl block mb-2"></i>
            <div class="font-playfair text-2xl font-bold text-hotel-dark">{{ $value }}</div>
            <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-0.5">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Quick Search Bar moved into the hero section above --}}

{{-- ==========================================
     FEATURED ROOMS
     ========================================== --}}
<div class="container mx-auto px-4 md:px-6 mb-20">
    <div class="text-center mb-12">
        <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Our Accommodations</span>
        <h2 class="font-playfair text-3xl md:text-4xl font-bold text-hotel-dark mt-2">Our Room Types</h2>
        <p class="text-gray-500 text-[0.95rem] mt-3 max-w-xl mx-auto">From standard comfort to deluxe double rooms — each room crafted for a peaceful stay in Phnom Penh.</p>
    </div>

    @php
        $roomImages = [
            'standard_twin'   => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80',
            'standard_double' => 'https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=600&q=80',
            'deluxe_double'   => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=600&q=80',
        ];
    @endphp

    @if($featuredRooms->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredRooms as $room)
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.07)] hover:shadow-[0_12px_35px_rgba(0,0,0,0.13)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col">
                <div class="relative overflow-hidden h-[220px]">
                    <img src="{{ $roomImages[$room->roomType?->slug] ?? $roomImages['standard_double'] }}"
                         alt="{{ $room->displayType() }}"
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-4 left-4">
                        <span class="bg-hotel-dark/80 backdrop-blur-sm text-hotel-gold text-xs font-bold px-3 py-1 rounded-full">
                            {{ $room->displayType() }}
                        </span>
                    </div>
                </div>
                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-gray-500 text-xs"><i class="bi bi-people mr-1"></i>Up to {{ $room->roomType?->capacity }} guests</span>
                        <div class="font-playfair text-2xl font-bold text-hotel-gold">
                            ${{ number_format($room->roomType?->price_per_night ?? 0, 0) }}
                            <span class="text-[0.75rem] text-gray-400 font-sans font-normal">/night</span>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed mb-5 flex-grow">
                        {{ Str::limit($room->roomType?->description ?? 'A comfortable and well-appointed room for your stay in Phnom Penh.', 80) }}
                    </p>
                    <a href="{{ route('rooms.show', $room) }}"
                       class="w-full text-center bg-hotel-dark hover:bg-hotel-accent text-white font-semibold py-2.5 rounded-xl transition-colors duration-200">
                        View Details & Book
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center border-[1.5px] border-hotel-dark text-hotel-dark hover:bg-hotel-dark hover:text-white font-semibold px-8 py-3 rounded-xl transition-colors duration-200">
                <i class="bi bi-grid mr-2"></i> View All Rooms
            </a>
        </div>
    @else
        {{-- Fallback if no rooms yet --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($roomTypes as $slug => $label)
            <div class="bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.07)] flex flex-col">
                <img src="{{ $roomImages[$slug] ?? $roomImages['standard_double'] }}" alt="{{ $label }}" class="w-full h-[220px] object-cover">
                <div class="p-6 flex-grow">
                    <h5 class="font-bold text-xl text-hotel-dark mb-2">{{ $label }}</h5>
                    <a href="{{ route('rooms.index', ['type' => $slug]) }}" class="w-full text-center block bg-hotel-dark hover:bg-hotel-accent text-white font-semibold py-2.5 rounded-xl transition-colors duration-200 mt-4">
                        View Rooms
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ==========================================
     WHY CHOOSE US
     ========================================== --}}
<div class="bg-hotel-light py-20 mb-0">
    <div class="container mx-auto px-4 md:px-6">
        <div class="text-center mb-12">
            <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Why Us?</span>
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-hotel-dark mt-2">Experience Dara Meas</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['bi-wifi',         'Free Wi-Fi',       'High-speed internet in all rooms and public areas.'],
                ['bi-shield-check', 'Safe & Secure',    '24-hour security and reception, always at your service.'],
                ['bi-qr-code-scan', 'Easy Payment',     'Convenient ABA PayWay QR code payments accepted.'],
                ['bi-clock',        '24/7 Check-In',    'Flexible check-in times to match your schedule.'],
            ] as [$icon, $title, $desc])
            <div class="text-center p-6 bg-white rounded-2xl shadow-sm border border-[#f0ebe2]">
                <div class="w-14 h-14 rounded-full bg-hotel-gold/10 flex items-center justify-center mx-auto mb-4">
                    <i class="bi {{ $icon }} text-hotel-gold text-2xl"></i>
                </div>
                <h5 class="font-bold text-lg text-hotel-dark mb-2">{{ $title }}</h5>
                <p class="text-gray-500 text-[0.88rem] leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ==========================================
     CONTACT CTA
     ========================================== --}}
<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-16 text-white text-center">
    <div class="container mx-auto px-4 md:px-6">
        <h2 class="font-playfair text-3xl md:text-4xl font-bold mb-4">Ready to Book Your Stay?</h2>
        <p class="text-white/70 mb-8 text-[1.05rem]">Reserve your room today and experience the warmth of Dara Meas hospitality.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-hotel-gold hover:bg-[#b8935a] text-hotel-dark font-bold px-8 py-3.5 rounded-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                <i class="bi bi-calendar-check mr-2"></i> Book Now
            </a>
            <a href="{{ route('contact') }}" class="inline-flex items-center bg-white/10 hover:bg-white/20 text-white font-bold px-8 py-3.5 rounded-xl border border-white/25 transition-all duration-300">
                <i class="bi bi-telephone mr-2"></i> Contact Us
            </a>
        </div>
    </div>
</div>

@endsection
