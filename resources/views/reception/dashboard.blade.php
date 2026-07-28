@extends('layouts.reception')

@section('title', 'Reception Dashboard')
@section('page_title', 'Reception Desk')

@section('content')

<div class="p-5 md:p-8 space-y-6 bg-gray-50/30 min-h-screen" x-data="{ 
        activeTab: 'arrivals',
        showSettleModal: false, 
        settleBookingId: null, 
        settleAmount: 0, 
        settleQrUrl: '', 
        settleActionUrl: '', 
        openSettleModal(bookingId, amount, qrUrl, actionUrl) { 
            this.settleBookingId = bookingId; 
            this.settleAmount = parseFloat(amount).toFixed(2); 
            this.settleQrUrl = qrUrl; 
            this.settleActionUrl = actionUrl; 
            this.showSettleModal = true; 
        } 
    }">

    {{-- ==========================================
         FLASH ALERTS
         ========================================== --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition class="flex justify-between items-center bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <i class="bi bi-check-circle-fill text-emerald-600"></i>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 ml-4 shrink-0 transition-colors"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="bi bi-exclamation-circle-fill text-red-500"></i>
                </div>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-400 hover:text-red-600 ml-4 shrink-0 transition-colors"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 shadow-sm">
            <div class="flex items-center gap-2 font-semibold text-sm mb-2"><i class="bi bi-exclamation-triangle text-red-500"></i> Please fix the following errors:</div>
            <ul class="list-disc list-inside text-sm pl-4 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ==========================================
         HERO STAT CARDS (Medical Dashboard Style)
         ========================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        {{-- In-House Guests --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center"><i class="bi bi-house-door-fill text-teal-600 text-lg"></i></div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-teal-600 bg-teal-50 px-2.5 py-1 rounded-full">Live</span>
            </div>
            <div>
                <div class="font-playfair text-3xl font-bold text-gray-800 leading-none">{{ $inHouseGuests->count() }}</div>
                <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-1.5">In-House Guests</div>
            </div>
        </div>

        {{-- Arrivals Today --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center"><i class="bi bi-box-arrow-in-right text-emerald-500 text-lg"></i></div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">Today</span>
            </div>
            <div>
                <div class="font-playfair text-3xl font-bold text-gray-800 leading-none">{{ $arrivalsToday->count() }}</div>
                <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-1.5">Arrivals Today</div>
            </div>
        </div>

        {{-- Departures Today --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center"><i class="bi bi-box-arrow-right text-amber-500 text-lg"></i></div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full">Today</span>
            </div>
            <div>
                <div class="font-playfair text-3xl font-bold text-gray-800 leading-none">{{ $todayDepartures->count() }}</div>
                <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-1.5">Departures Today</div>
            </div>
        </div>

        {{-- Pending Room Service --}}
        <div class="bg-white rounded-2xl border {{ $pendingRoomServices->count() > 0 ? 'border-red-200 shadow-red-100/50' : 'border-gray-100' }} shadow-sm p-5 flex flex-col gap-3 hover:shadow-md transition-shadow relative overflow-hidden">
            @if($pendingRoomServices->count() > 0)
                <div class="absolute top-0 left-0 w-full h-1 bg-red-500 animate-pulse"></div>
            @endif
            <div class="flex items-center justify-between">
                <div class="w-10 h-10 rounded-full {{ $pendingRoomServices->count() > 0 ? 'bg-red-50' : 'bg-gray-50' }} flex items-center justify-center">
                    <i class="bi bi-bell-fill {{ $pendingRoomServices->count() > 0 ? 'text-red-500 animate-bounce' : 'text-gray-400' }} text-lg"></i>
                </div>
                @if($pendingRoomServices->count() > 0)
                    <span class="text-[0.65rem] font-bold uppercase tracking-widest text-red-600 bg-red-50 px-2.5 py-1 rounded-full">Action Req</span>
                @else
                    <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-400 bg-gray-50 px-2.5 py-1 rounded-full">Clear</span>
                @endif
            </div>
            <div>
                <div class="font-playfair text-3xl font-bold {{ $pendingRoomServices->count() > 0 ? 'text-red-600' : 'text-gray-800' }} leading-none">{{ $pendingRoomServices->count() }}</div>
                <div class="text-gray-400 text-xs font-semibold uppercase tracking-wider mt-1.5">Pending Requests</div>
            </div>
        </div>
    </div>

    {{-- ==========================================
         MAIN LAYOUT GRID (2/3 + 1/3)
         ========================================== --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
        
        {{-- ==========================================
             LEFT COLUMN: MAIN WORKSPACE (TABS)
             ========================================== --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                {{-- Segmented Tab Bar --}}
                <div class="flex flex-wrap items-center gap-1.5 p-3 border-b border-gray-100 bg-gray-50/50">
                    <button @click="activeTab = 'arrivals'"
                        :class="activeTab === 'arrivals' ? 'bg-white text-hotel-dark shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all">
                        <i class="bi bi-box-arrow-in-right" :class="activeTab === 'arrivals' ? 'text-emerald-500' : ''"></i> Arrivals
                        <span :class="activeTab === 'arrivals' ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-200 text-gray-500'" class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-md">{{ $upcomingArrivals->count() }}</span>
                    </button>

                    <button @click="activeTab = 'departures'"
                        :class="activeTab === 'departures' ? 'bg-white text-hotel-dark shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all">
                        <i class="bi bi-box-arrow-right" :class="activeTab === 'departures' ? 'text-amber-500' : ''"></i> Departures
                        <span :class="activeTab === 'departures' ? 'bg-amber-50 text-amber-600' : 'bg-gray-200 text-gray-500'" class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-md">{{ $todayDepartures->count() }}</span>
                    </button>

                    <button @click="activeTab = 'inhouse'"
                        :class="activeTab === 'inhouse' ? 'bg-white text-hotel-dark shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all">
                        <i class="bi bi-house-door" :class="activeTab === 'inhouse' ? 'text-teal-500' : ''"></i> In-House
                        <span :class="activeTab === 'inhouse' ? 'bg-teal-50 text-teal-600' : 'bg-gray-200 text-gray-500'" class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-md">{{ $inHouseGuests->count() }}</span>
                    </button>
                    
                    <button @click="activeTab = 'history'"
                        :class="activeTab === 'history' ? 'bg-white text-hotel-dark shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all">
                        <i class="bi bi-clock-history" :class="activeTab === 'history' ? 'text-purple-500' : ''"></i> History
                    </button>

                    @if($noShows->count() > 0)
                    <button @click="activeTab = 'noshows'"
                        :class="activeTab === 'noshows' ? 'bg-red-50 text-red-700 shadow-sm ring-1 ring-red-200' : 'text-red-500 hover:bg-red-50'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all ml-auto">
                        <i class="bi bi-person-x-fill"></i> No-Shows
                        <span class="bg-red-500 text-white text-[0.65rem] font-bold px-1.5 py-0.5 rounded-md">{{ $noShows->count() }}</span>
                    </button>
                    @endif
                </div>

                <div class="p-0">
                    {{-- TAB 1: ARRIVALS --}}
                    <div x-show="activeTab === 'arrivals'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @if($upcomingArrivals->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50/50 text-gray-500 uppercase tracking-wider text-[0.7rem] border-b border-gray-100">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">Guest</th>
                                        <th class="px-5 py-3 font-semibold">Status / Room</th>
                                        <th class="px-5 py-3 font-semibold">Balance</th>
                                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($upcomingArrivals as $booking)
                                    <tr class="hover:bg-gray-50/30 transition-colors">
                                        <td class="px-5 py-3.5">
                                            <div class="font-bold text-gray-800">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                            <div class="text-hotel-gold font-bold text-xs mt-0.5">{{ $booking->referenceNumber() }}</div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-2 mb-1">
                                                @if($booking->check_in_date->isToday())
                                                    <span class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-[0.65rem] font-bold px-2 py-0.5 rounded">TODAY</span>
                                                @elseif($booking->check_in_date->isTomorrow())
                                                    <span class="bg-blue-50 border border-blue-200 text-blue-700 text-[0.65rem] font-bold px-2 py-0.5 rounded">TOMORROW</span>
                                                @else
                                                    <span class="text-gray-500 text-xs font-semibold">{{ $booking->check_in_date->format('M d') }}</span>
                                                @endif
                                                <span class="text-gray-400 text-xs px-1">•</span>
                                                <span class="text-gray-600 font-medium text-xs">{{ $booking->room?->roomType?->display_name ?? 'Unassigned' }}</span>
                                            </div>
                                            <div class="text-gray-500 text-xs font-medium"><i class="bi bi-door-closed mr-1 text-gray-400"></i>Room {{ $booking->room?->room_number ?? '—' }}</div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            @php $paid = $booking->totalPaid() + 0.01 >= (float) $booking->total_price; @endphp
                                            @if($paid)
                                                <span class="inline-flex items-center gap-1 text-emerald-600 text-xs font-bold"><i class="bi bi-check-circle-fill"></i> Settled</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-amber-600 text-xs font-bold"><i class="bi bi-exclamation-circle-fill"></i> Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                            @if(!$paid)
                                                @php
                                                    $remaining = max(0, $booking->total_price - $booking->totalPaid());
                                                    $qrString = \App\Services\KhqrGenerator::forAmount($remaining, '');
                                                    $qrPath = (new \chillerlan\QRCode\QRCode)->render($qrString);
                                                @endphp
                                                <button type="button" @click="openSettleModal({{ $booking->id }}, {{ $remaining }}, '{{ $qrPath }}', '{{ route('reception.payment.manual', $booking->id) }}')"
                                                        class="inline-flex items-center gap-1 bg-white border border-amber-200 hover:bg-amber-50 text-amber-700 font-semibold px-3 py-1.5 rounded text-xs transition-colors shadow-sm">
                                                    Settle
                                                </button>
                                            @endif
                                            @if($booking->check_in_date->startOfDay()->lte(now()->startOfDay()))
                                                <form action="{{ route('reception.checkin', $booking->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Check in this guest?')"
                                                            class="inline-flex items-center gap-1 bg-hotel-gold hover:bg-[#b8935a] text-white font-semibold px-4 py-1.5 rounded text-xs transition-colors shadow-sm">
                                                        Check In
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3"><i class="bi bi-box-arrow-in-right text-gray-300 text-2xl"></i></div>
                            <p class="text-gray-500 font-medium">No upcoming arrivals.</p>
                        </div>
                        @endif
                    </div>

                    {{-- TAB 2: DEPARTURES --}}
                    <div x-show="activeTab === 'departures'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @if($todayDepartures->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50/50 text-gray-500 uppercase tracking-wider text-[0.7rem] border-b border-gray-100">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">Guest</th>
                                        <th class="px-5 py-3 font-semibold">Room</th>
                                        <th class="px-5 py-3 font-semibold">Balance</th>
                                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($todayDepartures as $booking)
                                    <tr class="hover:bg-gray-50/30 transition-colors">
                                        <td class="px-5 py-3.5">
                                            <div class="font-bold text-gray-800">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                            <div class="text-hotel-gold font-bold text-xs mt-0.5">{{ $booking->referenceNumber() }}</div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="text-gray-700 font-bold">Room {{ $booking->room?->room_number ?? '—' }}</div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            @php $paid = $booking->totalPaid() + 0.01 >= (float) $booking->total_price; @endphp
                                            @if($paid)
                                                <span class="inline-flex items-center gap-1 text-emerald-600 text-xs font-bold"><i class="bi bi-check-circle-fill"></i> Settled</span>
                                            @else
                                                @php
                                                    $remaining = max(0, $booking->total_price - $booking->totalPaid());
                                                    $qrString = \App\Services\KhqrGenerator::forAmount($remaining, '');
                                                    $qrPath = (new \chillerlan\QRCode\QRCode)->render($qrString);
                                                @endphp
                                                <button type="button" @click="openSettleModal({{ $booking->id }}, {{ $remaining }}, '{{ $qrPath }}', '{{ route('reception.payment.manual', $booking->id) }}')"
                                                        class="inline-flex items-center gap-1 bg-white border border-red-200 hover:bg-red-50 text-red-600 font-semibold px-3 py-1.5 rounded text-xs transition-colors shadow-sm animate-pulse">
                                                    Settle $<span x-text="'{{ number_format($remaining, 2) }}'"></span>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                            <a href="{{ route('reception.receipt', $booking->id) }}" target="_blank" class="inline-flex items-center gap-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold px-3 py-1.5 rounded text-xs transition-colors shadow-sm"><i class="bi bi-printer"></i></a>
                                            <form action="{{ route('reception.checkout', $booking->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Check out this guest?')"
                                                        class="inline-flex items-center gap-1 bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 rounded text-xs transition-colors shadow-sm">
                                                    Check Out
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3"><i class="bi bi-box-arrow-right text-gray-300 text-2xl"></i></div>
                            <p class="text-gray-500 font-medium">No departures today.</p>
                        </div>
                        @endif
                    </div>

                    {{-- TAB 3: IN-HOUSE --}}
                    <div x-show="activeTab === 'inhouse'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @if($inHouseGuests->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50/50 text-gray-500 uppercase tracking-wider text-[0.7rem] border-b border-gray-100">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">Room</th>
                                        <th class="px-5 py-3 font-semibold">Guest</th>
                                        <th class="px-5 py-3 font-semibold">Dates</th>
                                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($inHouseGuests as $booking)
                                    <tr class="hover:bg-gray-50/30 transition-colors">
                                        <td class="px-5 py-3.5">
                                            <div class="text-gray-800 font-bold text-base">{{ $booking->room?->room_number ?? '—' }}</div>
                                            <div class="text-[0.65rem] text-teal-600 font-bold uppercase tracking-wider mt-0.5">Occupied</div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="font-bold text-gray-800">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                            <div class="text-gray-400 text-xs">{{ $booking->referenceNumber() }}</div>
                                        </td>
                                        <td class="px-5 py-3.5 text-xs">
                                            <div class="text-gray-600 font-medium">
                                                {{ $booking->check_in_date?->format('M d') }} <i class="bi bi-arrow-right text-gray-400 mx-1"></i> {{ $booking->check_out_date?->format('M d, Y') }}
                                                @if($booking->check_out_date->startOfDay()->lt(today()))
                                                    <span class="ml-2 bg-red-100 text-red-700 animate-pulse border border-red-300 font-bold px-1.5 py-0.5 rounded text-[0.65rem] tracking-wider uppercase inline-block">Overstay</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 text-right space-x-1.5 whitespace-nowrap" x-data="{ showExtend: false }">
                                            @php
                                                $limit = $extensionLimits[$booking->id] ?? ['max_nights' => 30, 'next_booking' => null];
                                                $blocked = $limit['max_nights'] === 0;
                                            @endphp
                                            
                                            <div class="inline-flex relative items-center">
                                                @if(!$blocked)
                                                    <button @click="showExtend = !showExtend" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold px-3 py-1.5 rounded text-xs transition-colors shadow-sm mr-1.5">Extend</button>
                                                @else
                                                    <a href="{{ route('reception.relocate.show', $booking->id) }}" class="bg-white border border-purple-200 hover:bg-purple-50 text-purple-700 font-semibold px-3 py-1.5 rounded text-xs transition-colors shadow-sm mr-1.5">Relocate</a>
                                                @endif
                                                <a href="{{ route('reception.receipt', $booking->id) }}" target="_blank" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold px-3 py-1.5 rounded text-xs transition-colors shadow-sm mr-1.5" title="Print Receipt"><i class="bi bi-printer"></i></a>
                                                
                                                <form action="{{ route('reception.checkout', $booking->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Check out this guest early?')"
                                                            class="inline-flex items-center gap-1 bg-gray-800 hover:bg-gray-900 text-white font-semibold px-3 py-1.5 rounded text-xs transition-colors shadow-sm">
                                                        Check Out
                                                    </button>
                                                </form>

                                                {{-- Extend Popover --}}
                                                <div x-show="showExtend" @click.away="showExtend = false" x-cloak class="absolute right-0 top-full mt-2 w-64 bg-white border border-gray-200 shadow-xl rounded-xl p-4 z-50 text-left">
                                                    <form action="{{ route('reception.extend-stay', $booking->id) }}" method="POST">
                                                        @csrf
                                                        <div class="mb-3">
                                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Nights to extend</label>
                                                            <input type="number" name="extra_nights" min="1" max="{{ $limit['max_nights'] }}" value="1" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-hotel-gold outline-none">
                                                            <div class="text-[0.65rem] text-gray-400 mt-1">Max available: {{ $limit['max_nights'] }}</div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Payment</label>
                                                            <select name="payment_method" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-hotel-gold outline-none">
                                                                <option value="khqr">KHQR</option>
                                                                <option value="cash">Cash</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="w-full bg-hotel-gold text-white text-xs font-bold py-2 rounded transition-colors hover:bg-[#b8935a]">Confirm Extension</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3"><i class="bi bi-house text-gray-300 text-2xl"></i></div>
                            <p class="text-gray-500 font-medium">No in-house guests currently.</p>
                        </div>
                        @endif
                    </div>
                    
                    {{-- TAB 4: HISTORY --}}
                    <div x-show="activeTab === 'history'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        @if($recentHistory->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50/50 text-gray-500 uppercase tracking-wider text-[0.7rem] border-b border-gray-100">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">Ref</th>
                                        <th class="px-5 py-3 font-semibold">Guest</th>
                                        <th class="px-5 py-3 font-semibold">Status</th>
                                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($recentHistory as $booking)
                                    <tr class="hover:bg-gray-50/30 transition-colors">
                                        <td class="px-5 py-3.5"><span class="font-bold text-gray-800">{{ $booking->referenceNumber() }}</span></td>
                                        <td class="px-5 py-3.5"><div class="font-semibold text-gray-800">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div></td>
                                        <td class="px-5 py-3.5">
                                            @if($booking->booking_status === \App\Models\Booking::STATUS_CANCELLED)
                                                <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded">Cancelled</span>
                                            @elseif($booking->booking_status === \App\Models\Booking::STATUS_CHECKED_OUT)
                                                <span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">Checked Out</span>
                                            @elseif($booking->booking_status === \App\Models\Booking::STATUS_NO_SHOW)
                                                <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">No Show</span>
                                            @else
                                                <span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{{ ucfirst(str_replace('_', ' ', $booking->booking_status)) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                            <a href="{{ route('reception.receipt', $booking->id) }}" target="_blank" class="inline-flex items-center gap-1 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold px-3 py-1.5 rounded text-xs transition-colors shadow-sm"><i class="bi bi-printer"></i> Receipt</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-12"><p class="text-gray-500 font-medium">No recent history.</p></div>
                        @endif
                    </div>
                    
                    {{-- TAB 5: NO SHOWS --}}
                    @if($noShows->count() > 0)
                    <div x-show="activeTab === 'noshows'" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-red-50/50 text-red-700 uppercase tracking-wider text-[0.7rem] border-b border-red-100">
                                    <tr>
                                        <th class="px-5 py-3 font-semibold">Ref</th>
                                        <th class="px-5 py-3 font-semibold">Guest</th>
                                        <th class="px-5 py-3 font-semibold">Was Due</th>
                                        <th class="px-5 py-3 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($noShows as $booking)
                                    <tr class="hover:bg-red-50/20 transition-colors">
                                        <td class="px-5 py-3.5"><span class="font-bold text-gray-800">{{ $booking->referenceNumber() }}</span></td>
                                        <td class="px-5 py-3.5"><div class="font-semibold text-gray-800">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div></td>
                                        <td class="px-5 py-3.5 text-red-600 font-medium">{{ $booking->check_in_date->format('M d, Y') }}</td>
                                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                            <form action="{{ route('admin.bookings.cancel', $booking->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" onclick="return confirm('Cancel no-show and release room?')" class="inline-flex items-center gap-1 bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-3 py-1.5 rounded text-xs transition-colors"><i class="bi bi-x-circle"></i> Cancel</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ==========================================
             RIGHT COLUMN: ACTION CENTER
             ========================================== --}}
        <div class="xl:col-span-1 space-y-6">
            
            {{-- Pending Room Service --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col max-h-[500px]">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 shrink-0">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="bi bi-bell-fill text-hotel-gold"></i> Action Required</h3>
                    @if($pendingRoomServices->count() > 0)
                        <span class="bg-red-500 text-white text-[0.65rem] font-bold px-2 py-0.5 rounded-full">{{ $pendingRoomServices->count() }}</span>
                    @endif
                </div>
                
                <div class="p-4 space-y-3 overflow-y-auto">
                    @forelse($pendingRoomServices as $rs)
                        <div class="bg-red-50/50 border border-red-100 rounded-xl p-3.5 relative shadow-sm" x-data="{ showReply: false }">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-bold text-red-700 text-sm">Room {{ $rs->booking->room?->room_number ?? '—' }}</div>
                                <div class="text-[0.65rem] text-red-500 font-bold uppercase tracking-wider">{{ $rs->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="text-xs text-gray-700 font-medium mb-3">
                                @if($rs->requestedItems->isNotEmpty())
                                    {{ $rs->requestedItems->map(fn($i) => $i->amount_per_item . '× ' . ($i->catalog->item_name ?? 'Item'))->join(', ') }}
                                @endif
                                @if($rs->guest_notes)
                                    <div class="italic text-gray-500 mt-1.5 border-l-2 border-red-200 pl-2">&ldquo;{{ $rs->guest_notes }}&rdquo;</div>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('reception.room-service.complete', $rs->id) }}" method="POST" class="flex-1">
                                    @csrf @method('PATCH')
                                    <button type="submit" onclick="return confirm('Mark as completed?')" class="w-full bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold py-1.5 rounded-lg transition-colors flex justify-center items-center gap-1"><i class="bi bi-check2"></i> Complete</button>
                                </form>
                                <button type="button" @click="showReply = !showReply" class="bg-white border border-gray-200 hover:bg-gray-50 text-gray-500 px-3 rounded-lg transition-colors"><i class="bi bi-chat-dots text-xs"></i></button>
                            </div>
                            
                            {{-- Reply input --}}
                            <div x-show="showReply" x-cloak class="mt-3 pt-3 border-t border-red-100">
                                <form action="{{ route('reception.room-service.complete', $rs->id) }}" method="POST" class="flex gap-2">
                                    @csrf @method('PATCH')
                                    <input type="text" name="response" placeholder="Reply to guest..." class="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-red-300">
                                    <button type="submit" class="bg-hotel-dark text-white text-[0.65rem] font-bold px-3 rounded-lg hover:bg-black transition-colors">Send</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <i class="bi bi-check-circle text-gray-300 text-3xl block mb-2"></i>
                            <p class="text-gray-400 text-sm font-medium">All caught up!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Today's Overview (Mini Timeline) --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2"><i class="bi bi-activity text-teal-600"></i> Today's Overview</h3>
                </div>
                <div class="p-0">
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-50">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2"><i class="bi bi-box-arrow-in-right text-emerald-500 text-sm"></i> Remaining Arrivals</div>
                        <div class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">{{ $arrivalsToday->whereNotIn('booking_status', [\App\Models\Booking::STATUS_CHECKED_IN, \App\Models\Booking::STATUS_CHECKED_OUT])->count() }}</div>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-50">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2"><i class="bi bi-box-arrow-right text-amber-500 text-sm"></i> Remaining Departures</div>
                        <div class="text-sm font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">{{ $todayDepartures->count() }}</div>
                    </div>
                    <div class="flex items-center justify-between px-5 py-3.5">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2"><i class="bi bi-house-door text-teal-500 text-sm"></i> Rooms Occupied</div>
                        <div class="text-sm font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded">{{ $inHouseGuests->count() }}</div>
                    </div>
                </div>
                <div class="p-4 bg-gray-50/50 border-t border-gray-100">
                    <a href="{{ route('reception.walkin.create') }}" class="block w-full text-center bg-white border border-gray-200 hover:border-hotel-gold text-hotel-dark font-bold py-2.5 rounded-lg text-sm transition-colors shadow-sm">
                        <i class="bi bi-plus-circle mr-1"></i> New Walk-In
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- ==========================================
         SETTLE MODAL
         ========================================== --}}
    <div x-show="showSettleModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 text-left" @click.away="showSettleModal = false">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                <h3 class="text-xl font-bold font-playfair text-hotel-dark flex items-center gap-2"><i class="bi bi-wallet2 text-hotel-gold"></i> Settle Balance</h3>
                <button type="button" @click="showSettleModal = false" class="text-gray-400 hover:text-gray-600 transition-colors"><i class="bi bi-x-lg text-xl"></i></button>
            </div>
            
            <p class="text-gray-500 mb-4 text-center text-sm">Scan the QR code below or collect cash for the remaining balance.</p>
            <div class="text-center mb-5">
                <span class="block text-xs uppercase tracking-wider font-semibold text-gray-400 mb-1">Amount Due</span>
                <span class="text-3xl font-bold text-red-600">$<span x-text="settleAmount"></span></span>
            </div>
            
            <div class="flex justify-center mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <img :src="settleQrUrl" alt="QR Code" class="w-48 h-48 rounded-lg shadow-sm border border-gray-200 object-contain bg-white p-2">
            </div>

            <form :action="settleActionUrl" method="POST">
                @csrf
                <input type="hidden" name="amount_paid" :value="settleAmount">
                <input type="hidden" name="payment_for" value="booking">
                
                <div class="mb-6">
                    <label class="block text-xs font-semibold mb-2 uppercase tracking-wide text-gray-500">Payment Method Received</label>
                    <select name="payment_method" class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-hotel-gold outline-none bg-white font-medium text-gray-700">
                        <option value="khqr_aba">KHQR / ABA Static</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="showSettleModal = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-semibold text-sm transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-hotel-gold hover:bg-[#b8935a] text-white rounded-lg font-semibold text-sm shadow-md flex items-center gap-2 transition-colors"><i class="bi bi-check2-circle"></i> Confirm Paid</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
