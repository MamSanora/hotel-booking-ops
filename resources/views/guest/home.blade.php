@extends('layouts.public')

@section('title', 'Dara Meas Hotel — Phnom Penh, Cambodia')
@section('meta_description', 'Book your stay at Dara Meas Hotel, a comfortable hotel in the heart of Phnom Penh, Cambodia. Standard Twin, Double, and Deluxe rooms from $35/night.')

@section('content')

{{-- ==========================================
    BACKGROUND IMAGE SECTION
     ========================================== --}}
<div class="relative min-h-[85vh] flex items-center overflow-hidden bg-hotel-dark" style="background-image: url('{{ asset('images/dara_meas_hero_lobby.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="absolute inset-0 bg-hotel-dark/80" style="background: linear-gradient(135deg, rgba(26,26,46,0.88) 0%, rgba(26,26,46,0.72) 55%, rgba(26,26,46,0.35) 100%);"></div>

    <div class="relative z-10 container mx-auto px-4 md:px-6 py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- LEFT: Hero text & CTA buttons --}}
            <div>
                <span class="inline-flex items-center gap-1.5 text-hotel-gold font-semibold uppercase tracking-widest text-xs mb-4 border border-hotel-gold/40 bg-hotel-gold/15 px-4 py-1.5 rounded-full backdrop-blur-sm">
                    <i class="bi bi-geo-alt-fill text-[0.8rem]"></i> Toul Kork, Phnom Penh &middot; 2-Star Boutique Hospitality
                </span>
                <h1 class="font-playfair text-5xl lg:text-[3.8rem] font-extrabold text-white leading-tight mb-6">
                    Warm Hospitality.<br>Authentic Comfort.
                </h1>
                <p class="text-white/80 text-lg leading-relaxed mb-10 max-w-lg font-light">
                    Welcome to Dara Meas Hotel — a clean, welcoming 2-star boutique hotel located right in Toul Kork, Phnom Penh. Enjoy 47 spotless rooms, 24/7 service, and transparent rates starting at $35/night.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-gradient-to-r from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-hotel-dark font-bold px-8 py-3.5 rounded-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_8px_25px_rgba(200,169,110,0.45)] text-[1.05rem]">
                        <i class="bi bi-door-open mr-2"></i> Browse Our Rooms
                    </a>
                    <a href="{{ route('about') }}" class="inline-flex items-center bg-white/10 hover:bg-white/20 text-white font-bold px-8 py-3.5 rounded-xl border border-white/25 hover:border-white/40 transition-all duration-300 text-[1.05rem] backdrop-blur-sm">
                        <i class="bi bi-info-circle mr-2"></i> About Us
                    </a>
                </div>
            </div>

            {{-- RIGHT: Check Availability card --}}
            <div class="bg-white/15 backdrop-blur-xl border border-white/25 rounded-2xl p-6 md:p-7 shadow-[0_12px_45px_rgba(0,0,0,0.35)]">
                <h3 class="font-playfair text-xl font-bold text-white mb-1 flex items-center">
                    <i class="bi bi-calendar2-check text-hotel-gold mr-2.5"></i>Check Availability
                </h3>
                <p class="text-white/70 text-sm mb-5">Find real-time availability and best rates for your stay.</p>
                <form action="{{ route('rooms.index') }}" method="GET" class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block font-semibold text-[0.72rem] uppercase text-white/80 tracking-wider mb-1.5">Check-In Date</label>
                        <input type="date" name="checkin" min="{{ date('Y-m-d') }}"
                               class="w-full border border-white/30 bg-white/15 text-white rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/30 transition-all outline-none placeholder-white/40 [color-scheme:dark]">
                    </div>
                    <div>
                        <label class="block font-semibold text-[0.72rem] uppercase text-white/80 tracking-wider mb-1.5">Check-Out Date</label>
                        <input type="date" name="checkout"
                               class="w-full border border-white/30 bg-white/15 text-white rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/30 transition-all outline-none [color-scheme:dark]">
                    </div>
                    <div>
                        <label class="block font-semibold text-[0.72rem] uppercase text-white/80 tracking-wider mb-1.5">Room Type</label>
                        <select name="type" class="w-full border border-white/30 bg-hotel-dark/80 text-white rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/30 transition-all outline-none">
                            <option value="">All Room Types ($35 - $80)</option>
                            <option value="standard_twin">Standard Twin ($35/night)</option>
                            <option value="standard_double">Standard Double ($50/night)</option>
                            <option value="deluxe_double">Deluxe Double ($80/night)</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-hotel-dark font-bold rounded-xl px-4 py-3.5 mt-1 transition-all duration-300 flex items-center justify-center gap-2 hover:shadow-[0_6px_20px_rgba(200,169,110,0.5)] hover:-translate-y-0.5">
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

{{-- ==========================================
     CAMBODIAN TRUST & PAYMENT SECURITY BAR (POINT 4)
     ========================================== --}}
<div class="container mx-auto px-4 md:px-6 mb-16">
    <div class="bg-gradient-to-r from-hotel-dark via-[#24243e] to-hotel-dark rounded-2xl p-6 md:p-7 text-white shadow-[0_10px_30px_rgba(26,26,46,0.18)] border border-white/10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-center divide-y sm:divide-y-0 sm:divide-x divide-white/10 text-center sm:text-left">
            
            <div class="flex items-center justify-center sm:justify-start gap-3.5 pt-3 sm:pt-0">
                <div class="w-11 h-11 rounded-xl bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                    <i class="bi bi-qr-code"></i>
                </div>
                <div>
                    <h5 class="font-bold text-white text-sm">ABA PayWay & KHQR</h5>
                    <p class="text-white/60 text-[0.75rem] mt-0.5">Instant Bakong & KHQR scan accepted at check-in & online.</p>
                </div>
            </div>

            <div class="flex items-center justify-center sm:justify-start gap-3.5 pt-3 sm:pt-0 sm:pl-6">
                <div class="w-11 h-11 rounded-xl bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h5 class="font-bold text-white text-sm">No Hidden Fees</h5>
                    <p class="text-white/60 text-[0.75rem] mt-0.5">Transparent pricing. All room taxes & amenities included.</p>
                </div>
            </div>

            <div class="flex items-center justify-center sm:justify-start gap-3.5 pt-3 sm:pt-0 lg:pl-6">
                <div class="w-11 h-11 rounded-xl bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                    <i class="bi bi-calendar2-check-fill"></i>
                </div>
                <div>
                    <h5 class="font-bold text-white text-sm">Instant Confirmation</h5>
                    <p class="text-white/60 text-[0.75rem] mt-0.5">Real-time room reservation with direct SMS & email invoice.</p>
                </div>
            </div>

            <div class="flex items-center justify-center sm:justify-start gap-3.5 pt-3 sm:pt-0 lg:pl-6">
                <div class="w-11 h-11 rounded-xl bg-hotel-gold/20 border border-hotel-gold/30 text-hotel-gold flex items-center justify-center text-xl flex-shrink-0 shadow-sm">
                    <i class="bi bi-award"></i>
                </div>
                <div>
                    <h5 class="font-bold text-white text-sm">Best Rate Guarantee</h5>
                    <p class="text-white/60 text-[0.75rem] mt-0.5">Direct booking rates from $35/night with free high-speed Wi-Fi.</p>
                </div>
            </div>

        </div>
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
            'standard_twin'   => asset('images/dara_room_twin.png'),
            'standard_double' => asset('images/dara_room_double.png'),
            'deluxe_double'   => asset('images/dara_room_deluxe.png'),
        ];
    @endphp

    @if($featuredRooms->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featuredRooms as $room)
            <div class="group bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.07)] hover:shadow-[0_12px_35px_rgba(0,0,0,0.13)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col">
                <div class="relative overflow-hidden h-[220px]">
                    <img src="{{ $roomImages[$room->roomType?->slug] ?? $roomImages['standard_double'] }}"
                         alt="{{ $room->displayType() }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-4 left-4 z-10">
                        <span class="bg-hotel-dark/85 backdrop-blur-sm text-hotel-gold border border-hotel-gold/30 text-xs font-bold px-3 py-1 rounded-full shadow-md">
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
            <div class="group bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.07)] hover:shadow-[0_12px_35px_rgba(0,0,0,0.13)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col">
                <div class="relative overflow-hidden h-[220px]">
                    <img src="{{ $roomImages[$slug] ?? $roomImages['standard_double'] }}" alt="{{ $label }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
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
     PRIME LOCATION & NEIGHBORHOOD GUIDE (POINT 3)
     ========================================== --}}
<div class="bg-white py-20 border-t border-b border-[#f0ebe2]">
    <div class="container mx-auto px-4 md:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            {{-- LEFT: Proximity Breakdown Card --}}
            <div class="lg:col-span-7 bg-hotel-dark text-white rounded-3xl p-8 md:p-10 shadow-[0_15px_45px_rgba(26,26,46,0.25)] relative overflow-hidden">
                <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-hotel-gold/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <span class="inline-flex items-center gap-1.5 bg-hotel-gold/20 text-hotel-gold border border-hotel-gold/30 text-xs font-bold uppercase tracking-widest px-3.5 py-1.5 rounded-full mb-6">
                    <i class="bi bi-pin-map-fill"></i> Neighborhood Guide
                </span>
                <h2 class="font-playfair text-3xl md:text-4xl font-extrabold mb-4 leading-tight">
                    Prime Location in Toul Kork, Phnom Penh
                </h2>
                <p class="text-white/75 text-sm md:text-base leading-relaxed mb-8 max-w-xl font-light">
                    Situated in the peaceful residential and commercial district of Toul Kork, Dara Meas Hotel offers the perfect balance: quiet nights and effortless connectivity to Phnom Penh's bustling landmarks and business centers.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 transition-all duration-300 hover:bg-white/15 hover:border-hotel-gold/50">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-hotel-gold text-hotel-dark flex items-center justify-center font-bold text-lg flex-shrink-0 mt-0.5 shadow-md">
                                <i class="bi bi-airplane-fill"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Phnom Penh Airport (PNH)</h4>
                                <p class="text-hotel-gold text-xs font-semibold mt-0.5">15–20 Mins by Car</p>
                                <p class="text-white/60 text-[0.75rem] mt-1">Private Tuk-Tuk or SUV transfer arranged by front desk.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 transition-all duration-300 hover:bg-white/15 hover:border-hotel-gold/50">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-hotel-gold text-hotel-dark flex items-center justify-center font-bold text-lg flex-shrink-0 mt-0.5 shadow-md">
                                <i class="bi bi-bank2"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Royal Palace & Riverside</h4>
                                <p class="text-hotel-gold text-xs font-semibold mt-0.5">12–15 Mins via Russian Blvd</p>
                                <p class="text-white/60 text-[0.75rem] mt-1">Explore iconic heritage, museums, and riverside dining.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 transition-all duration-300 hover:bg-white/15 hover:border-hotel-gold/50">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-hotel-gold text-hotel-dark flex items-center justify-center font-bold text-lg flex-shrink-0 mt-0.5 shadow-md">
                                <i class="bi bi-bag-check-fill"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Central Market & Vattanac</h4>
                                <p class="text-hotel-gold text-xs font-semibold mt-0.5">8–10 Mins by Tuk-Tuk</p>
                                <p class="text-white/60 text-[0.75rem] mt-1">Quick access to shopping hubs and financial towers.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 transition-all duration-300 hover:bg-white/15 hover:border-hotel-gold/50">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-hotel-gold text-hotel-dark flex items-center justify-center font-bold text-lg flex-shrink-0 mt-0.5 shadow-md">
                                <i class="bi bi-cup-hot-fill"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Toul Kork Cafés & Dining</h4>
                                <p class="text-hotel-gold text-xs font-semibold mt-0.5">2 Mins Walk</p>
                                <p class="text-white/60 text-[0.75rem] mt-1">Surrounded by local bakeries, markets, and eateries.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Concierge & Hospitality Highlights --}}
            <div class="lg:col-span-5 flex flex-col justify-center space-y-6">
                <div>
                    <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-1 block">Always at Your Service</span>
                    <h3 class="font-playfair text-3xl font-bold text-hotel-dark mt-1 mb-3">
                        Seamless & Stress-Free Stay
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Whether you are arriving late from an international flight or visiting Phnom Penh for a regional conference, our dedicated 2-star boutique amenities keep you relaxed and connected.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-hotel-light border border-[#e8e0d0] transition-all duration-200 hover:shadow-md">
                        <div class="w-12 h-12 rounded-xl bg-hotel-dark text-hotel-gold flex items-center justify-center text-xl flex-shrink-0">
                            <i class="bi bi-headset"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-hotel-dark text-base">24/7 Front Desk Concierge</h5>
                            <p class="text-gray-500 text-xs leading-relaxed mt-0.5">Our friendly Khmer and English-speaking team is available day and night to assist with luggage, city directions, and express check-in.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-hotel-light border border-[#e8e0d0] transition-all duration-200 hover:shadow-md">
                        <div class="w-12 h-12 rounded-xl bg-hotel-dark text-hotel-gold flex items-center justify-center text-xl flex-shrink-0">
                            <i class="bi bi-p-square-fill"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-hotel-dark text-base">Secure On-Site Parking</h5>
                            <p class="text-gray-500 text-xs leading-relaxed mt-0.5">Complimentary, private parking right on our ground level for up to 15 vehicles and motorbikes with 24-hour CCTV monitoring.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-hotel-light border border-[#e8e0d0] transition-all duration-200 hover:shadow-md">
                        <div class="w-12 h-12 rounded-xl bg-hotel-dark text-hotel-gold flex items-center justify-center text-xl flex-shrink-0">
                            <i class="bi bi-router-fill"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-hotel-dark text-base">High-Speed Fiber Wi-Fi</h5>
                            <p class="text-gray-500 text-xs leading-relaxed mt-0.5">Reliable, complimentary wireless internet throughout all guest rooms, lobby, and ground-floor dining area.</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- ==========================================
     VERIFIED GUEST TESTIMONIALS (POINT 4)
     ========================================== --}}
<div class="bg-hotel-light py-20 border-b border-[#e8e0d0]">
    <div class="container mx-auto px-4 md:px-6">
        <div class="text-center mb-14">
            <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Real Experiences</span>
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-hotel-dark mt-2">What Our Guests Say</h2>
            <p class="text-gray-500 text-[0.95rem] mt-2 max-w-lg mx-auto">Genuine feedback from business travelers and tourists who made Dara Meas Hotel their home in Phnom Penh.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Testimonial 1 --}}
            <div class="bg-white rounded-2xl p-7 shadow-sm border border-[#f0ebe2] flex flex-col justify-between relative hover:shadow-md transition-shadow">
                <div class="text-hotel-gold text-sm flex gap-1 mb-4">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-6 italic font-light flex-grow">
                    "Super clean rooms and very quiet at night! The split A/C works great, and having secure parking right in front for my car was a huge plus. Will definitely stay here again for business trips to Toul Kork."
                </p>
                <div class="flex items-center gap-3.5 pt-4 border-t border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-hotel-gold/15 text-hotel-dark font-bold flex items-center justify-center text-sm">
                        SV
                    </div>
                    <div>
                        <h5 class="font-bold text-hotel-dark text-sm">Sophea V.</h5>
                        <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                            <i class="bi bi-patch-check-fill text-emerald-500"></i> Verified Stay &middot; Standard Twin
                        </p>
                    </div>
                </div>
            </div>

            {{-- Testimonial 2 --}}
            <div class="bg-white rounded-2xl p-7 shadow-sm border border-[#f0ebe2] flex flex-col justify-between relative hover:shadow-md transition-shadow">
                <div class="text-hotel-gold text-sm flex gap-1 mb-4">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-6 italic font-light flex-grow">
                    "Unbeatable value for $50/night! The staff at the front desk arranged an early morning tuk-tuk to the Royal Palace and gave fantastic recommendations for local bakeries just around the corner."
                </p>
                <div class="flex items-center gap-3.5 pt-4 border-t border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-hotel-gold/15 text-hotel-dark font-bold flex items-center justify-center text-sm">
                        MT
                    </div>
                    <div>
                        <h5 class="font-bold text-hotel-dark text-sm">Marcus T.</h5>
                        <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                            <i class="bi bi-patch-check-fill text-emerald-500"></i> Verified Stay &middot; Standard Double
                        </p>
                    </div>
                </div>
            </div>

            {{-- Testimonial 3 --}}
            <div class="bg-white rounded-2xl p-7 shadow-sm border border-[#f0ebe2] flex flex-col justify-between relative hover:shadow-md transition-shadow">
                <div class="text-hotel-gold text-sm flex gap-1 mb-4">
                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-6 italic font-light flex-grow">
                    "We booked two Deluxe Double rooms for a family weekend. Spotless bathrooms, high-speed fiber Wi-Fi that actually worked across all floors, and seamless check-in with ABA PayWay QR code!"
                </p>
                <div class="flex items-center gap-3.5 pt-4 border-t border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-hotel-gold/15 text-hotel-dark font-bold flex items-center justify-center text-sm">
                        CK
                    </div>
                    <div>
                        <h5 class="font-bold text-hotel-dark text-sm">Channary K.</h5>
                        <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                            <i class="bi bi-patch-check-fill text-emerald-500"></i> Verified Stay &middot; Deluxe Double
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <span class="inline-flex items-center gap-2 bg-white px-5 py-2 rounded-full text-xs font-semibold text-gray-600 border border-gray-200 shadow-sm">
                <i class="bi bi-shield-lock-fill text-hotel-gold text-sm"></i> 100% Authentic Guest Reviews collected after verified checkout.
            </span>
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
