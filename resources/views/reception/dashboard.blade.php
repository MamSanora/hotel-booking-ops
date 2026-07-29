@extends('layouts.reception')

@section('title', 'Reception Dashboard')
@section('page_title', 'Reception Desk')

@section('content')

<div class="p-5 md:p-8 space-y-8">

    {{-- ==========================================
         FLASH ALERTS
         ========================================== --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="flex justify-between items-center bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <i class="bi bi-check-circle-fill text-emerald-600"></i>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 ml-4 shrink-0 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="bi bi-exclamation-circle-fill text-red-500"></i>
                </div>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-400 hover:text-red-600 ml-4 shrink-0 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
            <div class="flex items-center gap-2 font-semibold text-sm mb-2">
                <i class="bi bi-exclamation-triangle text-red-500"></i> Please fix the following errors:
            </div>
            <ul class="list-disc list-inside text-sm pl-4 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ==========================================
         HERO STAT CARDS
         ========================================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- In-House Guests --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3 hover:-translate-y-0.5 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="bi bi-house-door-fill text-blue-500 text-xl"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-blue-400 bg-blue-50 px-2 py-0.5 rounded-full">Live</span>
            </div>
            <div>
                <div class="font-playfair text-4xl font-bold text-hotel-dark leading-none">{{ $inHouseGuests->count() }}</div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">In-House Guests</div>
            </div>
        </div>

        {{-- Arrivals Today --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3 hover:-translate-y-0.5 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <i class="bi bi-box-arrow-in-right text-emerald-500 text-xl"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full">Today</span>
            </div>
            <div>
                <div class="font-playfair text-4xl font-bold text-hotel-dark leading-none">{{ $arrivalsToday->count() }}</div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">Arrivals Today</div>
            </div>
        </div>

        {{-- Departures Today --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3 hover:-translate-y-0.5 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center">
                    <i class="bi bi-box-arrow-right text-amber-500 text-xl"></i>
                </div>
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full">Today</span>
            </div>
            <div>
                <div class="font-playfair text-4xl font-bold text-hotel-dark leading-none">{{ $todayDepartures->count() }}</div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">Departures Today</div>
            </div>
        </div>

        {{-- Pending Room Service --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3 hover:-translate-y-0.5 transition-transform duration-200 {{ $pendingRoomServices->count() > 0 ? 'border-amber-300 ring-2 ring-amber-100' : '' }}">
            <div class="flex items-center justify-between">
                <div class="w-11 h-11 rounded-xl {{ $pendingRoomServices->count() > 0 ? 'bg-amber-100' : 'bg-gray-50' }} flex items-center justify-center">
                    <i class="bi bi-bell-fill {{ $pendingRoomServices->count() > 0 ? 'text-amber-500 animate-bounce' : 'text-gray-400' }} text-xl"></i>
                </div>
                @if($pendingRoomServices->count() > 0)
                    <span class="text-[0.65rem] font-bold uppercase tracking-widest text-white bg-amber-500 px-2 py-0.5 rounded-full animate-pulse">Alert</span>
                @else
                    <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Clear</span>
                @endif
            </div>
            <div>
                <div class="font-playfair text-4xl font-bold {{ $pendingRoomServices->count() > 0 ? 'text-amber-600' : 'text-hotel-dark' }} leading-none">{{ $pendingRoomServices->count() }}</div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">Pending Requests</div>
            </div>
        </div>

    </div>

    {{-- ==========================================
         2-COLUMN OPERATIONS LAYOUT
         Left (65%): Tabbed booking operations panel
         Right (35%): Today's movement + room service alerts (sticky sidebar)
         ========================================== --}}
    <div class="flex flex-col xl:flex-row gap-6 items-start">

        {{-- ── LEFT COLUMN: Tabbed Operations Panel ── --}}
        <div class="flex-1 min-w-0 space-y-5">

    {{-- Settle modal shared state — plain window object to avoid Alpine store timing issues.
         app.js calls Alpine.start() in <head> so alpine:init has already fired before any
         inline script in content runs. Using window avoids the race entirely. --}}
    <script>
        window.settleModal = { open: false, amount: '0.00', qrUrl: '', actionUrl: '' };
    </script>

    {{-- ==========================================
         TABBED OPERATIONS PANEL
         ========================================== --}}
    <div x-data="{ activeTab: 'arrivals' }" class="bg-white rounded-2xl border border-gray-200 shadow-sm relative">

        {{-- Tab Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50/60">
            <div class="flex flex-wrap gap-2">

                {{-- Tab: Upcoming Arrivals --}}
                <button type="button" @click="activeTab = 'arrivals'"
                    :class="activeTab === 'arrivals'
                        ? 'bg-hotel-gold text-white shadow-sm shadow-hotel-gold/30 font-bold'
                        : 'bg-white text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all">
                    <i class="bi bi-box-arrow-in-right" :class="activeTab === 'arrivals' ? 'text-white' : 'text-emerald-500'"></i>
                    Upcoming Arrivals
                    <span :class="activeTab === 'arrivals' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600'"
                          class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-full">{{ $upcomingArrivals->count() }}</span>
                </button>

                {{-- Tab: Today's Departures --}}
                <button type="button" @click="activeTab = 'departures'"
                    :class="activeTab === 'departures'
                        ? 'bg-hotel-gold text-white shadow-sm shadow-hotel-gold/30 font-bold'
                        : 'bg-white text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all">
                    <i class="bi bi-box-arrow-right" :class="activeTab === 'departures' ? 'text-white' : 'text-amber-500'"></i>
                    Today's Departures
                    <span :class="activeTab === 'departures' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600'"
                          class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-full">{{ $todayDepartures->count() }}</span>
                </button>

                {{-- Tab: In-House Guests --}}
                <button type="button" @click="activeTab = 'inhouse'"
                    :class="activeTab === 'inhouse'
                        ? 'bg-hotel-gold text-white shadow-sm shadow-hotel-gold/30 font-bold'
                        : 'bg-white text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all">
                    <i class="bi bi-house-door" :class="activeTab === 'inhouse' ? 'text-white' : 'text-blue-500'"></i>
                    In-House Guests
                    <span :class="activeTab === 'inhouse' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600'"
                          class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-full">{{ $inHouseGuests->count() }}</span>
                </button>

                {{-- Tab: Recent History --}}
                <button type="button" @click="activeTab = 'history'"
                    :class="activeTab === 'history'
                        ? 'bg-hotel-gold text-white shadow-sm shadow-hotel-gold/30 font-bold'
                        : 'bg-white text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all">
                    <i class="bi bi-clock-history" :class="activeTab === 'history' ? 'text-white' : 'text-purple-500'"></i>
                    Recent History
                    <span :class="activeTab === 'history' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600'"
                          class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-full">{{ $recentHistory->count() }}</span>
                </button>

                {{-- Tab: No-Shows (only show if any exist) --}}
                @if($noShows->count() > 0)
                <button type="button" @click="activeTab = 'noshows'"
                    :class="activeTab === 'noshows'
                        ? 'bg-red-600 text-white shadow-sm font-bold'
                        : 'bg-red-50 text-red-700 hover:bg-red-100 font-semibold border border-red-200'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all">
                    <i class="bi bi-person-x-fill"></i>
                    No-Shows
                    <span :class="activeTab === 'noshows' ? 'bg-white/20 text-white' : 'bg-red-100 text-red-700'"
                          class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-full animate-pulse">{{ $noShows->count() }}</span>
                </button>
                @endif

            </div>

            {{-- Context hint --}}
            <div class="text-xs text-gray-400 hidden lg:block">
                <span x-show="activeTab === 'arrivals'"><i class="bi bi-info-circle mr-1"></i>Confirmed bookings arriving today or later</span>
                <span x-show="activeTab === 'departures'" x-cloak><i class="bi bi-info-circle mr-1"></i>Checked-in guests scheduled out today</span>
                <span x-show="activeTab === 'inhouse'" x-cloak><i class="bi bi-info-circle mr-1"></i>Currently occupied rooms &amp; extensions</span>
                <span x-show="activeTab === 'history'" x-cloak><i class="bi bi-info-circle mr-1"></i>Activity over the last 14 days</span>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">

            {{-- ── TAB 1: UPCOMING ARRIVALS ── --}}
            <div x-show="activeTab === 'arrivals'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                @livewire('reception.upcoming-arrivals-list')
            </div>

            {{-- ── TAB 2: TODAY'S DEPARTURES ── --}}
            <div x-show="activeTab === 'departures'" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                @if($todayDepartures->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-[0.75rem] uppercase tracking-wider">
                                    <th class="px-4 py-3 font-semibold rounded-tl-xl rounded-bl-xl">Ref</th>
                                    <th class="px-4 py-3 font-semibold">Guest</th>
                                    <th class="px-4 py-3 font-semibold">Room</th>
                                    <th class="px-4 py-3 font-semibold">Balance</th>
                                    <th class="px-4 py-3 font-semibold rounded-tr-xl rounded-br-xl text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($todayDepartures as $booking)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="font-playfair text-hotel-gold font-bold text-base">{{ $booking->referenceNumber() }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-800 text-sm">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-gray-800 font-medium text-sm">Room {{ $booking->room?->room_number ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        @php $paid = $booking->totalPaid() + 0.01 >= (float) $booking->total_price; @endphp
                                        @if($paid)
                                            <span class="text-emerald-600 font-medium text-sm flex items-center gap-1.5">
                                                <i class="bi bi-check-circle-fill text-emerald-500"></i> Settled
                                            </span>
                                        @else
                                            @php
                                                $remaining = max(0, $booking->total_price - $booking->totalPaid());
                                                $qrString = $booking->room?->roomType?->use_mam_sanora_qr
                                                    ? \App\Services\KhqrGenerator::forMamSanora($remaining, $booking->referenceNumber())
                                                    : \App\Services\KhqrGenerator::forAmount($remaining, $booking->referenceNumber());
                                                $qrDataUri = (new \chillerlan\QRCode\QRCode)->render($qrString);
                                            @endphp
                                            <button type="button"
                                                    onclick="(function(el){
                                                        document.getElementById('settle-amount-display').textContent = el.dataset.settleAmount;
                                                        document.getElementById('settle-amount-input').value = el.dataset.settleAmount;
                                                        document.getElementById('settle-qr-img').src = el.dataset.settleQr;
                                                        document.getElementById('settle-form').action = el.dataset.settleAction;
                                                        window.dispatchEvent(new CustomEvent('settle-open'));
                                                    })(this)"
                                                    data-settle-amount="{{ number_format($remaining, 2, '.', '') }}"
                                                    data-settle-qr="{{ $qrDataUri }}"
                                                    data-settle-action="{{ route('reception.payment.manual', $booking->id) }}"
                                                    class="inline-flex items-center gap-1 bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-blue-200">
                                                <i class="bi bi-wallet2"></i> Settle
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('reception.receipt', $booking->id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-gray-200">
                                            <i class="bi bi-printer"></i> Receipt
                                        </a>
                                        <form action="{{ route('reception.checkout', $booking->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Check out this guest?')"
                                                    class="inline-flex items-center gap-1 bg-amber-100 hover:bg-amber-200 text-amber-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-amber-200">
                                                <i class="bi bi-door-closed"></i> Check Out
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-400">
                        <i class="bi bi-inbox text-4xl block mb-3 text-gray-200"></i>
                        <p class="text-sm">No departures scheduled for today.</p>
                    </div>
                @endif
            </div>

            {{-- ── TAB 3: IN-HOUSE GUESTS ── --}}
            <div x-show="activeTab === 'inhouse'" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                @if($inHouseGuests->count() > 0)
                    <div class="overflow-x-auto overflow-y-auto rounded-xl border border-gray-100" style="max-height: 520px;">
                        <table class="w-full text-left">
                            <thead class="sticky top-0 bg-gray-50 shadow-sm z-10">
                                <tr class="text-gray-500 text-[0.75rem] uppercase tracking-wider">
                                    <th class="px-4 py-3 font-semibold">Room</th>
                                    <th class="px-4 py-3 font-semibold">Guest</th>
                                    <th class="px-4 py-3 font-semibold">Check-In</th>
                                    <th class="px-4 py-3 font-semibold">Check-Out</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-4 py-3 font-semibold text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($inHouseGuests as $booking)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="font-bold text-gray-800">Room {{ $booking->room?->room_number ?? '—' }}</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-semibold text-gray-800 text-sm">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                        <div class="text-gray-400 text-xs mt-0.5">{{ $booking->guest?->phones?->first()?->phone_number ?? '—' }}</div>
                                        @if($booking->special_requests)
                                            <div class="mt-1.5 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1 text-amber-800 text-[0.75rem] flex items-start gap-1 max-w-xs">
                                                <i class="bi bi-chat-left-text-fill text-amber-500 shrink-0 mt-0.5 text-[0.7rem]"></i>
                                                <span>{{ $booking->special_requests }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-gray-700 text-sm whitespace-nowrap">{{ $booking->check_in_date?->format('M d, Y') }}</td>
                                    <td class="px-4 py-4 text-gray-700 text-sm whitespace-nowrap">{{ $booking->check_out_date?->format('M d, Y') }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">Checked In</span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-right relative">
                                        @php
                                            $limit     = $extensionLimits[$booking->id] ?? ['max_nights' => 30, 'next_booking' => null];
                                            $maxNights = $limit['max_nights'];
                                            $nextBook  = $limit['next_booking'];
                                            $blocked   = $maxNights === 0;
                                        @endphp

                                        <div class="flex items-center justify-end gap-2" x-data="{ showExtend: false }">

                                            {{-- Extend or Relocate --}}
                                            @if(!$blocked)
                                                <button type="button" @click="showExtend = !showExtend"
                                                        class="inline-flex items-center gap-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-emerald-200">
                                                    <i class="bi bi-calendar-plus"></i> Extend
                                                </button>
                                            @else
                                                <a href="{{ route('reception.relocate.show', $booking->id) }}"
                                                   class="inline-flex items-center gap-1 bg-purple-100 hover:bg-purple-200 text-purple-800 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-purple-200">
                                                    <i class="bi bi-arrow-repeat"></i> Relocate
                                                </a>
                                            @endif

                                            {{-- Receipt --}}
                                            <a href="{{ route('reception.receipt', $booking->id) }}" target="_blank"
                                               class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-gray-200">
                                                <i class="bi bi-printer"></i> Receipt
                                            </a>

                                            {{-- Check-Out --}}
                                            <form action="{{ route('reception.checkout', $booking->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @if($booking->check_out_date && $booking->check_out_date->isFuture() && !$booking->check_out_date->isToday())
                                                    <button type="submit"
                                                            onclick="return confirm('⚠️ EARLY DEPARTURE:\nRoom {{ $booking->room?->room_number ?? '' }} – scheduled until {{ $booking->check_out_date->format('M d, Y') }}.\nConfirm early check-out?')"
                                                            class="inline-flex items-center gap-1 bg-orange-100 hover:bg-orange-200 text-orange-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-orange-200">
                                                        <i class="bi bi-box-arrow-right"></i> Early Out
                                                    </button>
                                                @else
                                                    <button type="submit"
                                                            onclick="return confirm('Check out guest from Room {{ $booking->room?->room_number ?? '' }}?')"
                                                            class="inline-flex items-center gap-1 bg-amber-100 hover:bg-amber-200 text-amber-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-amber-200">
                                                        <i class="bi bi-door-closed"></i> Check Out
                                                    </button>
                                                @endif
                                            </form>

                                            {{-- Extension Blocked Warning --}}
                                            @if($blocked && $nextBook)
                                                <div class="absolute right-4 top-full mt-1 z-10 bg-red-50 border border-red-200 text-red-800 text-[0.75rem] rounded-xl px-3 py-2 shadow-lg w-64 text-left"
                                                     x-data x-init="setTimeout(() => $el.remove(), 8000)">
                                                    <i class="bi bi-exclamation-triangle-fill mr-1 text-red-500"></i>
                                                    <strong>Extension impossible.</strong><br>
                                                    Room {{ $booking->room?->room_number }} is reserved from
                                                    {{ $nextBook->check_in_date?->format('M d') }}.
                                                    Use <strong>Relocate</strong> to move this guest.
                                                </div>
                                            @endif

                                            {{-- Extend Stay Form --}}
                                            @if(!$blocked)
                                                <div x-show="showExtend" x-cloak
                                                     class="absolute right-4 mt-2 z-10 bg-white border border-emerald-200 rounded-2xl shadow-xl p-4 w-72"
                                                     style="top: auto;">
                                                    <form action="{{ route('reception.extend-stay', $booking->id) }}" method="POST">
                                                        @csrf
                                                        <p class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-1.5">
                                                            <i class="bi bi-calendar-plus text-emerald-600"></i>
                                                            Extend — Room {{ $booking->room?->room_number ?? '—' }}
                                                        </p>
                                                        @if($nextBook)
                                                            <p class="text-[0.75rem] text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-2.5 py-1.5 mb-3">
                                                                <i class="bi bi-info-circle mr-1"></i>
                                                                Max <strong>{{ $maxNights }} night(s)</strong> — next guest arrives {{ $nextBook->check_in_date?->format('M d') }}.
                                                            </p>
                                                        @endif
                                                        <div class="mb-3">
                                                            <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Extra Nights</label>
                                                            <input type="number" name="extra_nights" min="1" max="{{ $maxNights }}" value="1" required
                                                                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm font-bold text-center focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition-all">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Payment Method</label>
                                                            <select name="payment_method" required
                                                                    class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:border-emerald-500 outline-none transition-all">
                                                                <option value="cash">Cash</option>
                                                                <option value="khqr">KHQR</option>
                                                            </select>
                                                        </div>
                                                        <p class="text-xs text-gray-400 mb-3">
                                                            Rate: ${{ number_format($booking->room?->roomType?->price_per_night ?? 0, 2) }}/night
                                                        </p>
                                                        <div class="flex gap-2">
                                                            <button type="submit" onclick="return confirm('Extend stay and collect payment?')"
                                                                    class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-xl text-sm transition-colors">
                                                                Confirm
                                                            </button>
                                                            <button type="button" @click="showExtend = false"
                                                                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold py-2 rounded-xl text-sm transition-colors">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-400">
                        <i class="bi bi-building text-4xl block mb-3 text-gray-200"></i>
                        <p class="text-sm">No guests currently staying in the hotel.</p>
                    </div>
                @endif
            </div>

            {{-- ── TAB 4: RECENT HISTORY ── --}}
            <div x-show="activeTab === 'history'" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-playfair text-lg font-bold text-hotel-dark flex items-center gap-2">
                        <i class="bi bi-clock-history text-purple-500"></i> Recent History
                    </h3>
                    <span class="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-full font-medium">Last 14 Days</span>
                </div>

                @if($recentHistory->count() > 0)
                    <div class="overflow-x-auto overflow-y-auto rounded-xl border border-gray-100" style="max-height: 500px;">
                        <table class="w-full text-left">
                            <thead class="sticky top-0 bg-gray-50 shadow-sm z-10">
                                <tr class="text-gray-500 text-[0.75rem] uppercase tracking-wider">
                                    <th class="px-4 py-3 font-semibold">Ref</th>
                                    <th class="px-4 py-3 font-semibold">Guest</th>
                                    <th class="px-4 py-3 font-semibold">Room</th>
                                    <th class="px-4 py-3 font-semibold">Dates</th>
                                    <th class="px-4 py-3 font-semibold">Status</th>
                                    <th class="px-4 py-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentHistory as $booking)
                                <tr class="hover:bg-gray-50/60 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="font-playfair text-hotel-gold font-bold">{{ $booking->referenceNumber() }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800 text-sm">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                        <div class="text-gray-400 text-xs mt-0.5">{{ $booking->guest?->phones?->first()?->phone_number ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-700 text-sm font-medium">Room {{ $booking->room?->room_number ?? '—' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                                        {{ $booking->check_in_date?->format('M d') }} — {{ $booking->check_out_date?->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($booking->isCancelled())
                                            <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">Cancelled</span>
                                        @else
                                            <span class="bg-gray-200 text-gray-700 text-xs font-bold px-2.5 py-1 rounded-full">Checked Out</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('reception.receipt', $booking->id) }}" target="_blank" class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-gray-200">
                                            <i class="bi bi-printer"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-gray-400">
                        <i class="bi bi-clock-history text-4xl block mb-3 text-gray-200"></i>
                        <p class="text-sm">No recent history available.</p>
                    </div>
                @endif
            </div>

            {{-- ── TAB 5: NO-SHOWS ── --}}
            @if($noShows->count() > 0)
            <div x-show="activeTab === 'noshows'" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center">
                        <i class="bi bi-person-x-fill text-red-500"></i>
                    </div>
                    <div>
                        <h3 class="font-playfair text-lg font-bold text-hotel-dark">No-Shows</h3>
                        <p class="text-xs text-gray-400">Bookings that were confirmed but the guest never arrived. Use &ldquo;Cancel&rdquo; to release the room.</p>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-xl border border-red-100">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-red-50 text-red-700 text-[0.75rem] uppercase tracking-wider">
                                <th class="px-4 py-3 font-semibold">Ref</th>
                                <th class="px-4 py-3 font-semibold">Guest</th>
                                <th class="px-4 py-3 font-semibold">Was Due</th>
                                <th class="px-4 py-3 font-semibold">Room</th>
                                <th class="px-4 py-3 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-50">
                            @foreach($noShows as $booking)
                            <tr class="hover:bg-red-50/40 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-playfair text-hotel-gold font-bold">{{ $booking->referenceNumber() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-800 text-sm">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                    <div class="text-gray-400 text-xs">{{ $booking->guest?->phones?->first()?->phone_number ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-red-700 text-sm">{{ $booking->check_in_date->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $booking->check_in_date->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-700 text-sm font-medium">Room {{ $booking->room?->room_number ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form action="{{ route('reception.bookings.cancel', $booking->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                onclick="return confirm('Cancel no-show booking {{ $booking->referenceNumber() }} and release the room?')"
                                                class="inline-flex items-center gap-1.5 bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-red-200">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>{{-- end tab content --}}

    </div>{{-- end tabbed panel --}}

        </div>{{-- end LEFT COLUMN --}}

        {{-- ── RIGHT COLUMN: Today's Movement + Room Service Alerts ── --}}
        <div class="w-full xl:w-80 shrink-0 space-y-5 xl:sticky xl:top-6">

            {{-- Section header --}}
            <h2 class="font-playfair text-lg font-bold text-hotel-dark flex items-center gap-2">
                <i class="bi bi-people-fill text-teal-500"></i>
                Today's Movement
                <span class="text-xs font-normal text-gray-400 ml-1">{{ now()->format('M j') }}</span>
            </h2>

            {{-- Guest Movement Cards Wrapper --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-1 gap-5">
                {{-- Check-Ins Today --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 bg-emerald-50/60">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <i class="bi bi-box-arrow-in-right text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800 text-sm">Check-Ins Today</div>
                            <div class="text-emerald-600 text-xs font-bold">{{ $arrivalsToday->count() }} guest{{ $arrivalsToday->count() !== 1 ? 's' : '' }} arriving</div>
                        </div>
                    </div>
                    @if($arrivalsToday->count() > 0)
                        <ul class="divide-y divide-gray-50 max-h-56 overflow-y-auto">
                            @foreach($arrivalsToday as $booking)
                            <li class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($booking->guest?->full_name ?? 'G', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-gray-800 truncate">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                        <div class="text-[0.65rem] text-gray-400">{{ $booking->referenceNumber() }}</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <div class="text-xs font-bold text-gray-700">Rm {{ $booking->room?->room_number ?? '—' }}</div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-6 text-center text-gray-400">
                            <i class="bi bi-calendar-check text-2xl block mb-1 text-gray-200"></i>
                            <p class="text-xs">No arrivals today.</p>
                        </div>
                    @endif
                </div>

                {{-- Check-Outs Today --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 bg-red-50/60">
                        <div class="w-8 h-8 rounded-xl bg-red-100 text-red-500 flex items-center justify-center shrink-0">
                            <i class="bi bi-box-arrow-right text-sm"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800 text-sm">Check-Outs Today</div>
                            <div class="text-red-500 text-xs font-bold">{{ $todayDepartures->count() }} guest{{ $todayDepartures->count() !== 1 ? 's' : '' }} departing</div>
                        </div>
                    </div>
                    @if($todayDepartures->count() > 0)
                        <ul class="divide-y divide-gray-50 max-h-56 overflow-y-auto">
                            @foreach($todayDepartures as $booking)
                            <li class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($booking->guest?->full_name ?? 'G', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-gray-800 truncate">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                        <div class="text-[0.65rem] text-gray-400">{{ $booking->referenceNumber() }}</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <div class="text-xs font-bold text-gray-700">Rm {{ $booking->room?->room_number ?? '—' }}</div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-6 text-center text-gray-400">
                            <i class="bi bi-calendar-x text-2xl block mb-1 text-gray-200"></i>
                            <p class="text-xs">No departures today.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pending Housekeeping Request (right column) --}}
            @livewire('reception.housekeeping-requests-list')

        </div>{{-- end RIGHT COLUMN --}}

    </div>{{-- end 2-column layout --}}


    {{-- =====================================================
         SETTLE BALANCE MODAL
         Uses window.settleModal — decoupled from tabbed panel.
         ===================================================== --}}
    <div x-data="{ open: false }"
         x-show="open"
         @settle-open.window="open = true"
         class="fixed inset-0 z-[200] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 text-left mx-4" @click.outside="open = false">
            <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                <h3 class="text-xl font-bold font-playfair text-hotel-dark flex items-center gap-2">
                    <i class="bi bi-wallet2 text-hotel-gold"></i> Settle Balance
                </h3>
                <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>

            <p class="text-gray-500 mb-4 text-center text-sm">Scan the QR code below or collect cash for the remaining balance.</p>
            <div class="text-center mb-5">
                <span class="block text-xs uppercase tracking-wider font-semibold text-gray-400 mb-1">Amount Due</span>
                <span class="text-3xl font-bold text-red-600">$<span id="settle-amount-display"></span></span>
            </div>

            <!-- QR Code -->
            <div class="flex justify-center mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <img id="settle-qr-img" src="" alt="QR Code" class="w-48 h-48 rounded-lg shadow-sm border border-gray-200 object-contain bg-white p-2">
            </div>

            <form id="settle-form" action="" method="POST">
                @csrf
                <input type="hidden" id="settle-amount-input" name="amount_paid" value="">
                <input type="hidden" name="payment_for" value="booking">

                <div class="mb-6">
                    <label class="block text-xs font-semibold mb-2 uppercase tracking-wide text-gray-500">Payment Method Received</label>
                    <select name="payment_method" class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white font-medium text-gray-700">
                        <option value="khqr_aba">KHQR / ABA Static</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="open = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 transition-colors rounded-lg text-gray-700 font-semibold text-sm">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-br from-hotel-gold to-[#b8935a] hover:from-[#b8935a] hover:to-[#a07840] transition-all text-white rounded-lg font-semibold text-sm shadow-md flex items-center gap-2">
                        <i class="bi bi-check2-circle"></i> Confirm Paid
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>{{-- end outer page wrapper --}}

@endsection


