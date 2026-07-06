@extends('layouts.public')

@section('title', 'Blog & News — Dara Meas Hotel')

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 lg:py-16 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1542314831-c6a4d27ece11?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>
    
    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-3xl lg:text-[2.2rem] font-bold text-white mb-2">
            Hotel News & Travel Guides
        </h1>
        
        <nav aria-label="breadcrumb">
            <ol class="flex space-x-2 text-sm text-white/60">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a></li>
                <li class="text-white/30">/</li>
                <li class="text-hotel-gold" aria-current="page">Blog</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-16 lg:py-20">
    <div class="text-center max-w-2xl mx-auto mb-14">
        <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Latest Updates</span>
        <h2 class="font-playfair text-3xl md:text-4xl font-extrabold text-hotel-dark mt-2 mb-4">Discover Phnom Penh</h2>
        <p class="text-gray-600">Discover tips for exploring the city and highlights from the team at Dara Meas Hotel.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        {{-- Blog Post 1 --}}
        <article class="bg-white rounded-[18px] overflow-hidden border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.12)] transition-shadow duration-300 group">
            <div class="relative overflow-hidden aspect-[4/3]">
                <img src="{{ asset('images/blog1.jpg') }}" 
                     onerror="this.src='https://images.unsplash.com/photo-1574519946890-a54ff1a4336c?w=600&q=80'"
                     alt="Exploring Phnom Penh" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700">
                <div class="absolute top-4 left-4 bg-hotel-gold text-white text-xs font-bold uppercase tracking-wider py-1.5 px-3 rounded-lg">
                    Travel Guide
                </div>
            </div>
            <div class="p-6 md:p-8">
                <h3 class="font-playfair font-bold text-[1.4rem] text-hotel-dark mb-3 group-hover:text-hotel-gold transition-colors">
                    Exploring Phnom Penh
                </h3>
                <p class="text-gray-600 text-[0.95rem] leading-relaxed mb-6">
                    Discover top capital attractions near Dara Meas Hotel, including the Royal Palace, Wat Phnom, and vibrant riverside night markets.
                </p>
                <a href="#" class="text-hotel-dark font-semibold text-[0.9rem] flex items-center gap-2 hover:text-hotel-gold transition-colors">
                    Read More <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </article>

        {{-- Blog Post 2 --}}
        <article class="bg-white rounded-[18px] overflow-hidden border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.12)] transition-shadow duration-300 group">
            <div class="relative overflow-hidden aspect-[4/3]">
                <img src="{{ asset('images/blog2.jpg') }}" 
                     onerror="this.src='https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=600&q=80'"
                     alt="Dining at Dara Meas" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700">
                <div class="absolute top-4 left-4 bg-hotel-dark text-white text-xs font-bold uppercase tracking-wider py-1.5 px-3 rounded-lg">
                    Culinary
                </div>
            </div>
            <div class="p-6 md:p-8">
                <h3 class="font-playfair font-bold text-[1.4rem] text-hotel-dark mb-3 group-hover:text-hotel-gold transition-colors">
                    Dara Meas Restaurant
                </h3>
                <p class="text-gray-600 text-[0.95rem] leading-relaxed mb-6">
                    Enjoy authentic Cambodian flavors and international breakfasts prepared fresh daily at our ground-floor 40-seat restaurant.
                </p>
                <a href="#" class="text-hotel-dark font-semibold text-[0.9rem] flex items-center gap-2 hover:text-hotel-gold transition-colors">
                    Read More <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </article>

        {{-- Blog Post 3 --}}
        <article class="bg-white rounded-[18px] overflow-hidden border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_rgba(0,0,0,0.12)] transition-shadow duration-300 group">
            <div class="relative overflow-hidden aspect-[4/3]">
                <img src="{{ asset('images/blog3.jpg') }}" 
                     onerror="this.src='https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600&q=80'"
                     alt="Deluxe Double Room Experience" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700">
                <div class="absolute top-4 left-4 bg-[#7a5c00] text-white text-xs font-bold uppercase tracking-wider py-1.5 px-3 rounded-lg">
                    Luxury
                </div>
            </div>
            <div class="p-6 md:p-8">
                <h3 class="font-playfair font-bold text-[1.4rem] text-hotel-dark mb-3 group-hover:text-hotel-gold transition-colors">
                    Deluxe Double Room Experience
                </h3>
                <p class="text-gray-600 text-[0.95rem] leading-relaxed mb-6">
                    Unwind in our spacious Deluxe Double Rooms featuring sweeping city views and a luxurious king-size bed for ultimate relaxation.
                </p>
                <a href="#" class="text-hotel-dark font-semibold text-[0.9rem] flex items-center gap-2 hover:text-hotel-gold transition-colors">
                    Read More <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </article>

    </div>
</div>

@endsection
