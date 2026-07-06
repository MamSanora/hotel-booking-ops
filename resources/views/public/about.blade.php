@extends('layouts.public')

@section('title', 'About Us — Dara Meas Hotel')

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 lg:py-16 overflow-hidden">
    <!-- Background Image Overlay -->
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1542314831-c6a4d27ece11?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>
    
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

@endsection
