@extends('layouts.public')

@section('title', 'Gallery — Dara Meas Hotel')

@section('content')

{{-- ==========================================
     PAGE BANNER
     ========================================== --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 lg:py-16 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1542314831-c6a4d27ece11?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>
    
    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-3xl lg:text-[2.2rem] font-bold text-white mb-2">
            Hotel Gallery
        </h1>
        
        <nav aria-label="breadcrumb">
            <ol class="flex space-x-2 text-sm text-white/60">
                <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Home</a></li>
                <li class="text-white/30">/</li>
                <li class="text-hotel-gold" aria-current="page">Gallery</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-16 lg:py-24">
    <div class="text-center max-w-3xl mx-auto mb-16">
        <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Our Property</span>
        <h2 class="font-playfair text-3xl md:text-4xl font-extrabold text-hotel-dark mt-2 mb-4">Discover Dara Meas</h2>
        <p class="text-gray-600">Take a visual tour of our beautifully appointed rooms, elegant facilities, and the welcoming atmosphere that awaits you in the heart of Phnom Penh.</p>
    </div>

    @if($gallery->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($gallery as $item)
                <div class="group relative overflow-hidden rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 aspect-[4/3] cursor-pointer"
                     x-data="{ open: false }" 
                     @click="open = true">
                    
                    {{-- Thumbnail --}}
                    <img src="{{ asset('gallery/' . $item->image) }}" 
                         alt="Dara Meas Hotel Gallery" 
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                    
                    {{-- Hover Overlay --}}
                    <div class="absolute inset-0 bg-hotel-dark/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-hotel-gold text-white flex items-center justify-center text-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                            <i class="bi bi-zoom-in"></i>
                        </div>
                    </div>

                    {{-- Alpine.js Lightbox Modal --}}
                    <template x-teleport="body">
                        <div x-show="open" 
                             style="display: none;"
                             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4"
                             x-transition.opacity>
                            
                            {{-- Close Button --}}
                            <button @click="open = false" 
                                    class="absolute top-6 right-6 text-white/70 hover:text-white text-4xl leading-none transition-colors">
                                &times;
                            </button>
                            
                            {{-- Full Image --}}
                            <img src="{{ asset('gallery/' . $item->image) }}" 
                                 @click.outside="open = false"
                                 alt="Dara Meas Hotel Gallery Fullsize" 
                                 class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl">
                        </div>
                    </template>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-20 bg-gray-50 rounded-2xl border border-gray-200">
            <div class="text-gray-400 text-5xl mb-4"><i class="bi bi-images"></i></div>
            <h3 class="font-bold text-xl text-hotel-dark">No Images Yet</h3>
            <p class="text-gray-500 mt-2">Check back soon for stunning photos of our property.</p>
        </div>
    @endif
</div>

@endsection
