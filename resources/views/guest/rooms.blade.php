@extends('layouts.public')

@section('title', 'Our Rooms')
@section('meta_description', 'Browse all available rooms at Dara Meas Hotel — Standard Twin, Standard Double, Deluxe Double, Family Room and Suite.')

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

            <div>
                <label class="block font-semibold text-[0.75rem] uppercase text-gray-500 tracking-wider mb-2">Room Type</label>
                <select name="type" class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white">
                    <option value="">All Types</option>
                    <option value="standard_twin"   {{ request('type') === 'standard_twin'   ? 'selected' : '' }}>Standard Twin</option>
                    <option value="standard_double" {{ request('type') === 'standard_double' ? 'selected' : '' }}>Standard Double</option>
                    <option value="deluxe_double"   {{ request('type') === 'deluxe_double'   ? 'selected' : '' }}>Deluxe Double</option>
                    <option value="family_room"     {{ request('type') === 'family_room'     ? 'selected' : '' }}>Family Room</option>
                    <option value="suite"           {{ request('type') === 'suite'           ? 'selected' : '' }}>Suite</option>
                </select>
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
        @php
            $typeLabels = [
                'standard_twin'   => 'Standard Twin',
                'standard_double' => 'Standard Double',
                'deluxe_double'   => 'Deluxe Double',
                'family_room'     => 'Family Room',
                'suite'           => 'Suite',
            ];
        @endphp
        <a href="{{ route('rooms.index', request()->except('type')) }}"
           class="border-[1.5px] rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200
                  {{ !request('type') ? 'bg-hotel-dark border-hotel-dark text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-hotel-dark hover:border-hotel-dark hover:text-white' }}">
            All Rooms ({{ $rooms->count() }})
        </a>
        @foreach($typeLabels as $slug => $label)
            <a href="{{ route('rooms.index', array_merge(request()->all(), ['type' => $slug])) }}"
               class="border-[1.5px] rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-200
                      {{ request('type') === $slug ? 'bg-hotel-dark border-hotel-dark text-white' : 'border-gray-200 bg-white text-gray-600 hover:bg-hotel-dark hover:border-hotel-dark hover:text-white' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Results count --}}
    <p class="text-gray-500 text-sm mb-8">
        <i class="bi bi-grid mr-1"></i>
        Showing <strong class="text-gray-800">{{ $rooms->count() }}</strong> room(s)
        @if(request('checkin') && request('checkout'))
            available between
            <strong class="text-gray-800">{{ \Carbon\Carbon::parse(request('checkin'))->format('M d, Y') }}</strong> and
            <strong class="text-gray-800">{{ \Carbon\Carbon::parse(request('checkout'))->format('M d, Y') }}</strong>
        @endif
    </p>

    {{-- ==========================================
         ROOM GRID
         ========================================== --}}
    @php
        $roomImages = [
            'standard_twin'   => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80',
            'standard_double' => 'https://images.unsplash.com/photo-1631049552057-403cdb8f0658?w=600&q=80',
            'deluxe_double'   => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=600&q=80',
            'family_room'     => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=600&q=80',
            'suite'           => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=600&q=80',
        ];
    @endphp

    @if($rooms->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($rooms as $room)
                @php
                    $img = $roomImages[$room->room_type] ?? 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600&q=80';
                @endphp
                <div class="bg-white rounded-2xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.07)] hover:shadow-[0_12px_35px_rgba(0,0,0,0.13)] hover:-translate-y-1.5 transition-all duration-300 flex flex-col relative">

                    {{-- Status Badge --}}
                    @if($room->current_status === 'available')
                        <span class="absolute top-4 right-4 bg-green-500 text-white text-[0.72rem] font-bold px-3 py-1 rounded-full tracking-wider shadow-sm">Available</span>
                    @elseif($room->current_status === 'occupied')
                        <span class="absolute top-4 right-4 bg-red-500 text-white text-[0.72rem] font-bold px-3 py-1 rounded-full tracking-wider shadow-sm">Occupied</span>
                    @else
                        <span class="absolute top-4 right-4 bg-yellow-400 text-gray-900 text-[0.72rem] font-bold px-3 py-1 rounded-full tracking-wider shadow-sm">Maintenance</span>
                    @endif

                    <img src="{{ $img }}" alt="{{ $room->displayType() }}" class="w-full h-[220px] object-cover">

                    <div class="p-6 flex flex-col flex-grow">
                        {{-- Room Number & Price --}}
                        <div class="flex justify-between items-center mb-3">
                            <span class="bg-gray-50 text-gray-700 border border-gray-200 text-[0.75rem] px-2.5 py-1 rounded">
                                <i class="bi bi-hash mr-1"></i>Room {{ $room->room_number }}
                                &middot; <i class="bi bi-people mr-1"></i>Up to {{ $room->capacity }} guests
                            </span>
                            <div class="font-playfair text-2xl font-bold text-hotel-gold">
                                ${{ number_format($room->price_per_night, 0) }}
                                <span class="text-[0.8rem] text-gray-400 font-sans font-normal">/night</span>
                            </div>
                        </div>

                        <h5 class="font-bold text-xl text-hotel-dark mb-2">{{ $room->displayType() }}</h5>
                        <p class="text-gray-500 text-[0.88rem] leading-[1.6] mb-5 flex-grow">
                            {{ Str::limit($room->description ?? 'A comfortable and well-appointed room at Dara Meas Hotel, Phnom Penh.', 90) }}
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

                        {{-- Action Buttons --}}
                        <div class="flex gap-3">
                            <a href="{{ route('rooms.show', $room) }}" class="flex-1 text-center bg-transparent hover:bg-hotel-dark text-hotel-dark hover:text-white border-[1.5px] border-hotel-dark font-semibold text-[0.9rem] py-2 rounded-lg transition-colors duration-200">
                                Details
                            </a>
                            @if($room->current_status === 'available')
                                <a href="{{ route('rooms.show', $room) }}{{ request('checkin') ? '?checkin='.request('checkin').'&checkout='.request('checkout') : '' }}"
                                   class="flex-1 text-center bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] text-white font-semibold py-2 rounded-lg transition-all duration-300 hover:-translate-y-[1px] hover:shadow-[0_4px_15px_rgba(200,169,110,0.4)]">
                                    <i class="bi bi-calendar-plus mr-1"></i>Book
                                </a>
                            @else
                                <span class="flex-1 text-center bg-gray-50 text-gray-400 border border-gray-200 font-semibold text-[0.9rem] py-2 rounded-lg cursor-not-allowed">
                                    Unavailable
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
            <h4 class="font-bold text-2xl text-hotel-dark mb-3">No Rooms Available</h4>
            <p class="text-gray-500 mb-6">
                No rooms match your search for the selected dates and filters.<br>
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
</script>
@endpush
