@extends('layouts.public')

@section('title', 'About Us — Dara Meas Hotel')

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-hotel-dark py-12 lg:py-16 overflow-hidden" style="background-image: url('{{ asset('images/dara_meas_hero_lobby.png') }}'); background-size: cover; background-position: center;">
    <!-- Background Image Overlay -->
    <div class="absolute inset-0 bg-hotel-dark/90" style="background: linear-gradient(135deg, rgba(26,26,46,0.92) 0%, rgba(26,26,46,0.85) 100%);"></div>
    
    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-3xl lg:text-[2.2rem] font-bold text-white mb-2">
            About Us
        </h1>
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="flex space-x-2 text-sm text-white/60">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a></li>
                <li class="text-white/30">/</li>
                <li class="text-hotel-gold" aria-current="page">About Us</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-16 lg:py-24">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
        
        {{-- ==========================================
             LEFT: Image
             ========================================== --}}
        <div class="order-2 lg:order-1 relative group">
            <div class="absolute inset-0 bg-hotel-gold rounded-2xl transform translate-x-4 translate-y-4 group-hover:translate-x-6 group-hover:translate-y-6 transition-transform duration-500 opacity-20"></div>
            <img src="{{ asset('images/about.png') }}" 
                 onerror="this.src='https://images.unsplash.com/photo-1542314831-c6a4d27ece11?w=900&q=80'"
                 alt="Dara Meas Hotel Building" 
                 class="relative w-full h-[400px] lg:h-[550px] object-cover rounded-2xl shadow-xl z-10">
        </div>

        {{-- ==========================================
             RIGHT: Content
             ========================================== --}}
        <div class="order-1 lg:order-2">
            <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Welcome to Phnom Penh</span>
            <h2 class="font-playfair text-4xl lg:text-5xl font-extrabold text-hotel-dark mt-2 mb-6 leading-tight">
                About Dara Meas Hotel
            </h2>
            
            <p class="text-gray-600 leading-relaxed mb-5 text-[1.05rem]">
                Established in 2019 in Toul Kork, Phnom Penh, Cambodia, Dara Meas Hotel is a 2-star boutique property offering warm Cambodian hospitality, modern comfort, and executive convenience across 4 floors and 47 carefully crafted guest rooms.
            </p>
            
            <p class="text-gray-600 leading-relaxed mb-8 text-[1.05rem]">
                Spanning a 1,500 m² property with dedicated on-site parking for 15 vehicles, lush garden seating, 24-hour reception, and an authentic ground-floor restaurant, we ensure a smooth stay for leisure and business travelers alike.
            </p>
            
            <div class="grid grid-cols-2 gap-6 mb-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-hotel-light flex items-center justify-center text-hotel-gold text-xl shadow-sm">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-hotel-dark text-sm">Prime Location</h4>
                        <p class="text-xs text-gray-500">Heart of Phnom Penh</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-hotel-light flex items-center justify-center text-hotel-gold text-xl shadow-sm">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-hotel-dark text-sm">24/7 Service</h4>
                        <p class="text-xs text-gray-500">Always here for you</p>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('rooms.index') }}" 
               class="inline-block bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-bold py-3.5 px-8 rounded-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(200,169,110,0.45)]">
                Explore Our Rooms
            </a>
        </div>
        
    </div>
</div>

{{-- ==========================================
     NEIGHBORHOOD & PROXIMITY GUIDE
     ========================================== --}}
<div class="bg-hotel-light py-20 border-t border-[#e8e0d0]">
    <div class="container mx-auto px-4 md:px-6">
        <div class="max-w-3xl mx-auto text-center mb-12">
            <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Toul Kork District</span>
            <h2 class="font-playfair text-3xl md:text-4xl font-bold text-hotel-dark mt-2 mb-4">
                Neighborhood & Travel Times
            </h2>
            <p class="text-gray-600 leading-relaxed text-[1.02rem]">
                Located away from the high-traffic riverfront crowds yet only minutes from Phnom Penh's major business and cultural centers, Dara Meas Hotel provides tranquil rest with rapid citywide transit.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#f0ebe2] hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-hotel-dark text-hotel-gold flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-airplane-fill"></i>
                </div>
                <h4 class="font-bold text-hotel-dark text-lg mb-1">Phnom Penh Airport</h4>
                <p class="text-hotel-gold font-semibold text-xs mb-3">15–20 Minutes by Car</p>
                <p class="text-gray-500 text-xs leading-relaxed">Direct connection to Pochentong via Russian Boulevard. Airport Tuk-Tuk or SUV transfers available upon booking.</p>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#f0ebe2] hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-hotel-dark text-hotel-gold flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-bank2"></i>
                </div>
                <h4 class="font-bold text-hotel-dark text-lg mb-1">Royal Palace & Museum</h4>
                <p class="text-hotel-gold font-semibold text-xs mb-3">12–15 Minutes by Tuk-Tuk</p>
                <p class="text-gray-500 text-xs leading-relaxed">Visit the historic Royal Residence, Silver Pagoda, and the National Museum of Cambodia effortlessly.</p>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#f0ebe2] hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-hotel-dark text-hotel-gold flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-bag-check-fill"></i>
                </div>
                <h4 class="font-bold text-hotel-dark text-lg mb-1">Central Market</h4>
                <p class="text-hotel-gold font-semibold text-xs mb-3">8–10 Minutes by Tuk-Tuk</p>
                <p class="text-gray-500 text-xs leading-relaxed">Quick trip to Phsar Thmey (Central Market), Vattanac Capital Tower, and Canadia Bank headquarters.</p>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#f0ebe2] hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-hotel-dark text-hotel-gold flex items-center justify-center text-xl mb-4">
                    <i class="bi bi-cup-hot-fill"></i>
                </div>
                <h4 class="font-bold text-hotel-dark text-lg mb-1">Toul Kork Cafés</h4>
                <p class="text-hotel-gold font-semibold text-xs mb-3">2 Minutes Walk</p>
                <p class="text-gray-500 text-xs leading-relaxed">Surrounded by vibrant local coffee shops, supermarkets, pharmacies, and authentic Cambodian eateries right outside.</p>
            </div>
        </div>
    </div>
</div>

@endsection
