@extends('layouts.public')

@section('title', 'Gallery — Dara Meas Hotel')
@section('meta_description', 'Explore the Dara Meas Hotel gallery — view our rooms, facilities, balconies and more. A visual tour of our hotel in Sen Sok, Phnom Penh.')

@section('content')

{{-- PAGE BANNER --}}
<div class="relative bg-gradient-to-br from-hotel-dark to-hotel-accent py-14 lg:py-20 overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1542314831-c6a4d27ece11?w=1600&q=60')] bg-cover bg-center opacity-[0.08]"></div>
    <div class="container mx-auto px-4 md:px-6 relative z-10">
        <h1 class="font-playfair text-4xl lg:text-5xl font-bold text-white mb-4">
            <i class="bi bi-images mr-3 text-hotel-gold"></i>Hotel Gallery
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

<div class="container mx-auto px-4 md:px-6 py-12">
    <div class="text-center max-w-3xl mx-auto mb-10">
        <span class="text-hotel-gold font-bold uppercase tracking-widest text-xs mb-2 block">Our Property</span>
        <h2 class="font-playfair text-3xl md:text-4xl font-extrabold text-hotel-dark mt-2 mb-4">Discover Dara Meas</h2>
        <p class="text-gray-600">Take a visual tour of our beautifully appointed rooms, elegant facilities, and the welcoming atmosphere that awaits you in the heart of Phnom Penh.</p>
    </div>

    @php
        $uploadedImages = $gallery->toBase()->map(fn ($item) => [
            'src'      => asset('gallery_images/' . $item->image),
            'alt'      => 'Dara Meas Hotel',
            'category' => 'hotel',
        ]);

        $roomImages = collect([
            ['src' => asset('room/Standard Double 1.jpg'),    'alt' => 'Standard Room — Double Bed',    'category' => 'standard'],
            ['src' => asset('room/Standard Double 2.webp'),   'alt' => 'Standard Room — Double Bed 2',  'category' => 'standard'],
            ['src' => asset('room/Standard Double 3.jpg'),    'alt' => 'Standard Room — Double Bed 3',  'category' => 'standard'],
            ['src' => asset('room/Standard Twin 1.webp'),     'alt' => 'Standard Room — Twin Beds',     'category' => 'standard'],
            ['src' => asset('room/Standard Twin 2.webp'),     'alt' => 'Standard Room — Twin Beds 2',   'category' => 'standard'],
            ['src' => asset('room/Standard Twin 3.webp'),     'alt' => 'Standard Room — Twin Beds 3',   'category' => 'standard'],
            ['src' => asset('room/Standard Bathroom 1.jpg'),  'alt' => 'Standard Room — Bathroom',      'category' => 'standard'],
            ['src' => asset('room/Standard Bathroom 2.jpg'),  'alt' => 'Standard Room — Bathroom 2',    'category' => 'standard'],
            ['src' => asset('room/Deluxe Double 1.webp'),     'alt' => 'Deluxe Room — Double Bed',      'category' => 'deluxe'],
            ['src' => asset('room/Deluxe Double 2.webp'),     'alt' => 'Deluxe Room — Double Bed 2',    'category' => 'deluxe'],
            ['src' => asset('room/Deluxe Double 3.webp'),     'alt' => 'Deluxe Room — Double Bed 3',    'category' => 'deluxe'],
            ['src' => asset('room/Deluxe Double 4.webp'),     'alt' => 'Deluxe Room — Double Bed 4',    'category' => 'deluxe'],
            ['src' => asset('room/Deluxe Twin 1.webp'),       'alt' => 'Deluxe Room — Twin Beds',       'category' => 'deluxe'],
            ['src' => asset('room/Deluxe Twin 2.webp'),       'alt' => 'Deluxe Room — Twin Beds 2',     'category' => 'deluxe'],
            ['src' => asset('room/Deluxe Bathroom 1.webp'),   'alt' => 'Deluxe Room — Bathroom',        'category' => 'deluxe'],
            ['src' => asset('room/Deluxe Bathroom 2.webp'),   'alt' => 'Deluxe Room — Bathroom 2',      'category' => 'deluxe'],
            ['src' => asset('room/Family Triple Room 1.webp'),'alt' => 'Family Triple Room',            'category' => 'family'],
            ['src' => asset('room/Family Triple Room 2.webp'),'alt' => 'Family Triple Room 2',          'category' => 'family'],
            ['src' => asset('room/Family Triple Room 3.webp'),'alt' => 'Family Triple Room 3',          'category' => 'family'],
            ['src' => asset('room/Family Triple Room 4.webp'),'alt' => 'Family Triple Room 4',          'category' => 'family'],
            ['src' => asset('room/Family Triple Room 5.webp'),'alt' => 'Family Triple Room 5',          'category' => 'family'],
            ['src' => asset('room/Family Triple Room 6.webp'),'alt' => 'Family Triple Room 6',          'category' => 'family'],
            ['src' => asset('room/Balcony 1.webp'),           'alt' => 'Hotel Balcony View',            'category' => 'facilities'],
        ]);

        $allImages = $uploadedImages->merge($roomImages);
        $totalCount = $allImages->count();
    @endphp

    {{-- Category Filter Pills --}}
    <div class="flex flex-wrap gap-2 mb-8">
        @foreach([
            ['key' => 'all',        'label' => 'All Photos (' . $totalCount . ')'],
            ['key' => 'standard',   'label' => 'Standard Room'],
            ['key' => 'deluxe',     'label' => 'Deluxe Room'],
            ['key' => 'family',     'label' => 'Family Triple'],
            ['key' => 'facilities', 'label' => 'Facilities'],
            ['key' => 'hotel',      'label' => 'Hotel (' . $uploadedImages->count() . ')'],
        ] as $pill)
        <button type="button"
                onclick="filterGallery('{{ $pill['key'] }}')"
                id="pill-{{ $pill['key'] }}"
                class="border-[1.5px] rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200 border-gray-200 bg-white text-gray-600 hover:bg-hotel-dark hover:border-hotel-dark hover:text-white">
            {{ $pill['label'] }}
        </button>
        @endforeach
    </div>

    {{-- Photo Grid --}}
    @if($allImages->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" id="gallery-grid">
        @foreach($allImages as $img)
        <div class="group relative overflow-hidden rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 aspect-[4/3] cursor-pointer gallery-item"
             data-category="{{ $img['category'] }}"
             x-data="{ open: false }"
             @click="open = true">

            <img src="{{ $img['src'] }}"
                 alt="{{ $img['alt'] }}"
                 loading="lazy"
                 class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">

            <div class="absolute bottom-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <span class="text-[0.65rem] font-bold uppercase tracking-wider bg-black/60 text-white px-2 py-0.5 rounded-full backdrop-blur-sm">
                    {{ ucfirst($img['category']) }}
                </span>
            </div>

            <div class="absolute inset-0 bg-hotel-dark/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                <div class="w-12 h-12 rounded-full bg-hotel-gold text-white flex items-center justify-center text-xl transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                    <i class="bi bi-zoom-in"></i>
                </div>
            </div>

            <template x-teleport="body">
                <div x-show="open"
                     style="display: none;"
                     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/92 p-4"
                     x-transition.opacity
                     @keydown.escape.window="open = false">
                    <button @click="open = false"
                            class="absolute top-5 right-6 text-white/70 hover:text-white text-4xl leading-none transition-colors z-10">&times;</button>
                    <img src="{{ $img['src'] }}"
                         @click.outside="open = false"
                         alt="{{ $img['alt'] }}"
                         class="max-w-full max-h-[90vh] object-contain rounded-xl shadow-2xl">
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

@push('scripts')
<script>
    let activeFilter = 'all';

    function filterGallery(category) {
        activeFilter = category;

        // Update pill styles
        document.querySelectorAll('[id^="pill-"]').forEach(btn => {
            const isActive = btn.id === 'pill-' + category;
            btn.className = 'border-[1.5px] rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200 ' +
                (isActive
                    ? 'bg-hotel-dark border-hotel-dark text-white'
                    : 'border-gray-200 bg-white text-gray-600 hover:bg-hotel-dark hover:border-hotel-dark hover:text-white');
        });

        // Show/hide gallery items
        document.querySelectorAll('.gallery-item').forEach(item => {
            const show = category === 'all' || item.dataset.category === category;
            item.style.display = show ? '' : 'none';
        });
    }

    // Activate "All" pill on load
    document.addEventListener('DOMContentLoaded', () => filterGallery('all'));
</script>
@endpush
