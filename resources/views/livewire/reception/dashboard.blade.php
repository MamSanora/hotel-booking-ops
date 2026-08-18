<div class="px-5 py-6 md:px-8 space-y-8" wire:poll.15s>

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
        <div x-data="{ count: {{ $pendingRoomServices->count() }} }"
             @update-housekeeping-count.window="count = $event.detail.count"
             :class="count > 0 ? 'border-amber-300 ring-2 ring-amber-100' : ''"
             class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3 hover:-translate-y-0.5 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div :class="count > 0 ? 'bg-amber-100' : 'bg-gray-50'" class="w-11 h-11 rounded-xl flex items-center justify-center">
                    <i :class="count > 0 ? 'text-amber-500 animate-bounce' : 'text-gray-400'" class="bi bi-bell-fill text-xl"></i>
                </div>
                <template x-if="count > 0">
                    <span class="text-[0.65rem] font-bold uppercase tracking-widest text-white bg-amber-500 px-2 py-0.5 rounded-full animate-pulse">Alert</span>
                </template>
                <template x-if="count === 0">
                    <span class="text-[0.65rem] font-bold uppercase tracking-widest text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Clear</span>
                </template>
            </div>
            <div>
                <div x-text="count" :class="count > 0 ? 'text-amber-600' : 'text-hotel-dark'" class="font-playfair text-4xl font-bold leading-none"></div>
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mt-1">Pending Requests</div>
            </div>
        </div>

    </div>

    {{-- ==========================================
         STACKED OPERATIONS LAYOUT
         Top: Tabbed booking operations panel
         Bottom: Today's movement + room service alerts
         ========================================== --}}
    <div class="flex flex-col gap-6 items-start w-full">

        {{-- ── TOP SECTION: Tabbed Operations Panel ── --}}
        <div class="w-full space-y-5">

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
                    x-data="{ count: {{ $upcomingArrivalsCount }} }"
                    @update-arrivals-count.window="count = $event.detail.count"
                    :class="activeTab === 'arrivals'
                        ? 'bg-hotel-gold text-white shadow-sm shadow-hotel-gold/30 font-bold'
                        : 'bg-white text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200'"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs transition-all">
                    <i class="bi bi-box-arrow-in-right" :class="activeTab === 'arrivals' ? 'text-white' : 'text-emerald-500'"></i>
                    Upcoming Arrivals
                    <span :class="activeTab === 'arrivals' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600'"
                          class="text-[0.65rem] font-bold px-1.5 py-0.5 rounded-full" x-text="count"></span>
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
                    <div class="overflow-y-auto max-h-[600px] border border-gray-100 rounded-xl">
                        <table class="w-full text-left relative">
                            <thead class="sticky top-0 z-10 bg-gray-50 shadow-sm">
                                <tr class="text-gray-500 text-[0.75rem] uppercase tracking-wider">
                                    <th class="px-4 py-3 font-semibold rounded-tl-xl">Ref</th>
                                    <th class="px-4 py-3 font-semibold">Guest</th>
                                    <th class="px-4 py-3 font-semibold">Room</th>
                                    <th class="px-4 py-3 font-semibold">Balance</th>
                                    <th class="px-4 py-3 font-semibold rounded-tr-xl text-right">Action</th>
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
                                        @php $isMulti = $booking->bookingRooms->isNotEmpty(); @endphp
                                        @if($isMulti)
                                            <div x-data="{ 
                                                    open: false,
                                                    top: 0,
                                                    left: 0,
                                                    position() {
                                                        if (!this.$refs.btn) return;
                                                        const rect = this.$refs.btn.getBoundingClientRect();
                                                        this.top = rect.top - 8;
                                                        this.left = rect.left;
                                                    }
                                                 }" class="relative">
                                                <div class="text-gray-800 font-medium text-sm leading-snug">
                                                @php
                                                    $groupedBR = $booking->bookingRooms->groupBy('room_type_id');
                                                @endphp
                                                @foreach($groupedBR as $typeId => $typeRows)
                                                    <span>{{ $typeRows->first()->roomType?->display_name ?? '—' }}
                                                        @if($typeRows->count() > 1)<span class="text-hotel-gold font-bold">×{{ $typeRows->count() }}</span>@endif
                                                    </span>@if(!$loop->last)<span class="text-gray-300 mx-1">+</span>@endif
                                                @endforeach
                                                </div>
                                                <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => position())" @scroll.window="if(open) position()" @resize.window="if(open) position()"
                                                        class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                                    <i class="bi bi-door-closed"></i> View Rooms
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-transition x-cloak @click.outside="open = false"
                                                         :style="`top: ${top}px; left: ${left}px; transform: translateY(-100%);`"
                                                         class="fixed z-[9999] bg-white border border-gray-200 rounded-xl shadow-2xl p-3 w-56 text-xs">
                                                    <div class="font-bold text-gray-700 mb-2 flex items-center gap-1">
                                                        <i class="bi bi-building text-hotel-gold"></i> Assigned Rooms
                                                    </div>
                                                    @foreach($booking->bookingRooms as $br)
                                                        <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                                                            <span class="text-gray-600 font-medium">{{ $br->roomType?->display_name ?? '—' }}</span>
                                                            <span class="font-bold text-gray-900 flex items-center gap-1.5">
                                                                @if($br->room)
                                                                    Rm {{ $br->room->room_number }}
                                                                    @if($br->room->current_status === 'occupied')
                                                                        <button type="button" @click="$dispatch('open-confirm', { message: 'Mark Room {{ $br->room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $br->room->id }} }) })" title="Mark Vacated" class="text-amber-500 hover:text-amber-600 transition-colors bg-amber-50 px-1 py-0.5 rounded"><i class="bi bi-brush"></i></button>
                                                                    @endif
                                                                @else
                                                                    <span class="text-amber-600" title="Manage physically at front desk">TBA</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                </template>
                                            </div>
                                        @else
                                            <div class="text-gray-800 font-medium text-sm flex items-center gap-1.5">
                                                Room {{ $booking->room?->room_number ?? '—' }}
                                                @if($booking->room && $booking->room->current_status === 'occupied')
                                                    <button type="button" @click="$dispatch('open-confirm', { message: 'Mark Room {{ $booking->room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $booking->room->id }} }) })" title="Mark Vacated" class="text-amber-500 hover:text-amber-600 transition-colors bg-amber-50 px-1 py-0.5 rounded"><i class="bi bi-brush"></i></button>
                                                @endif
                                            </div>
                                        @endif
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
                                                    x-data
                                                    @click.prevent="$dispatch('settle-open', {
                                                        bookingId: {{ $booking->id }},
                                                        amount: '{{ number_format($remaining, 2, '.', '') }}',
                                                        qr: '{{ $qrDataUri }}'
                                                    })"
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
                                         <button type="button"
                                                 @click="$dispatch('open-checkout-modal', { bookingId: {{ $booking->id }} })"
                                                class="inline-flex items-center gap-1 bg-amber-100 hover:bg-amber-200 text-amber-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-amber-200">
                                            <i class="bi bi-door-closed"></i> Check Out
                                        </button>
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
                                        @php $isMulti = $booking->bookingRooms->isNotEmpty(); @endphp
                                        @if($isMulti)
                                            <div x-data="{ 
                                                    open: false,
                                                    top: 0,
                                                    left: 0,
                                                    position() {
                                                        if (!this.$refs.btn) return;
                                                        const rect = this.$refs.btn.getBoundingClientRect();
                                                        this.top = rect.top - 8;
                                                        this.left = rect.left;
                                                    }
                                                 }" class="relative">
                                                <div class="text-gray-800 font-medium text-sm leading-snug whitespace-normal max-w-xs">
                                                @php
                                                    $groupedBR = $booking->bookingRooms->groupBy('room_type_id');
                                                @endphp
                                                @foreach($groupedBR as $typeId => $typeRows)
                                                    <span>{{ $typeRows->first()->roomType?->display_name ?? '—' }}
                                                        @if($typeRows->count() > 1)<span class="text-hotel-gold font-bold">×{{ $typeRows->count() }}</span>@endif
                                                    </span>@if(!$loop->last)<span class="text-gray-300 mx-1">+</span>@endif
                                                @endforeach
                                                </div>
                                                <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => position())" @scroll.window="if(open) position()" @resize.window="if(open) position()"
                                                        class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                                    <i class="bi bi-door-closed"></i> View Rooms
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-transition x-cloak @click.outside="open = false"
                                                         :style="`top: ${top}px; left: ${left}px; transform: translateY(-100%);`"
                                                         class="fixed z-[9999] bg-white border border-gray-200 rounded-xl shadow-2xl p-3 w-56 text-xs">
                                                    <div class="font-bold text-gray-700 mb-2 flex items-center gap-1">
                                                        <i class="bi bi-building text-hotel-gold"></i> Assigned Rooms
                                                    </div>
                                                    @foreach($booking->bookingRooms as $br)
                                                        <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                                                            <span class="text-gray-600 font-medium">{{ $br->roomType?->display_name ?? '—' }}</span>
                                                            <span class="font-bold text-gray-900 flex items-center gap-1.5">
                                                                @if($br->room)
                                                                    Rm {{ $br->room->room_number }}
                                                                    @if($br->room->current_status === 'occupied')
                                                                        <button type="button" @click="$dispatch('open-confirm', { message: 'Mark Room {{ $br->room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $br->room->id }} }) })" title="Mark Vacated" class="text-amber-500 hover:text-amber-600 transition-colors bg-amber-50 px-1 py-0.5 rounded"><i class="bi bi-brush"></i></button>
                                                                    @endif
                                                                @else
                                                                    <span class="text-amber-600" title="Manage physically at front desk">TBA</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                </template>
                                            </div>
                                        @else
                                            <div class="text-gray-800 font-medium text-sm flex items-center gap-1.5">
                                                Room {{ $booking->room?->room_number ?? '—' }}
                                                @if($booking->room && $booking->room->current_status === 'occupied')
                                                    <button type="button" @click="$dispatch('open-confirm', { message: 'Mark Room {{ $booking->room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $booking->room->id }} }) })" title="Mark Vacated" class="text-amber-500 hover:text-amber-600 transition-colors bg-amber-50 px-1 py-0.5 rounded"><i class="bi bi-brush"></i></button>
                                                @endif
                                            </div>
                                        @endif
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
                                            @if(!$blocked)
                                                {{-- Extend Stay Temporarily Disabled
                                                <button type="button" @click="showExtend = !showExtend"
                                                        class="inline-flex items-center gap-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-emerald-200">
                                                    <i class="bi bi-calendar-plus"></i> Extend
                                                </button>
                                                --}}
                                            @else
                                                {{-- Relocate Temporarily Disabled: Development will pause on this until explicitly mentioned.
                                                <a href="{{ route('reception.relocate.show', $booking->id) }}"
                                                   class="inline-flex items-center gap-1 bg-purple-100 hover:bg-purple-200 text-purple-800 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-purple-200">
                                                    <i class="bi bi-arrow-repeat"></i> Relocate
                                                </a>
                                                --}}
                                            @endif
                                            <a href="{{ route('reception.receipt', $booking->id) }}" target="_blank"
                                               class="inline-flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-gray-200">
                                                <i class="bi bi-printer"></i> Receipt
                                            </a>
                                            @php
                                                $isEarly = $booking->check_out_date && $booking->check_out_date->isFuture() && !$booking->check_out_date->isToday();
                                            @endphp
                                            <button type="button"
                                                    @click="$dispatch('open-checkout-modal', { bookingId: {{ $booking->id }} })"
                                                    class="inline-flex items-center gap-1 {{ $isEarly ? 'bg-orange-100 hover:bg-orange-200 text-orange-700 border-orange-200' : 'bg-amber-100 hover:bg-amber-200 text-amber-700 border-amber-200' }} font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border">
                                                <i class="bi bi-door-closed"></i> {{ $isEarly ? 'Early Out' : 'Check Out' }}
                                            </button>
                                            {{-- 
                                            Extension & Relocate warnings temporarily disabled.
                                            Development will be paused on this until explicitly mentioned.
                                            @if($blocked && $nextBook)
                                                <div class="absolute right-4 top-full mt-1 z-10 bg-red-50 border border-red-200 text-red-800 text-[0.75rem] rounded-xl px-3 py-2 shadow-lg w-64 text-left"
                                                     x-data x-init="setTimeout(() => $el.remove(), 8000)">
                                                    <i class="bi bi-exclamation-triangle-fill mr-1 text-red-500"></i>
                                                    <strong>Extension impossible.</strong><br>
                                                    Room {{ $booking->room?->room_number }} is reserved from {{ $nextBook->check_in_date?->format('M d') }}.
                                                    Use <strong>Relocate</strong> to move this guest.
                                                </div>
                                            @endif
                                            --}}
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
                                        @php $isMulti = $booking->bookingRooms->isNotEmpty(); @endphp
                                        @if($isMulti)
                                            <div x-data="{ 
                                                    open: false,
                                                    top: 0,
                                                    left: 0,
                                                    position() {
                                                        if (!this.$refs.btn) return;
                                                        const rect = this.$refs.btn.getBoundingClientRect();
                                                        this.top = rect.top - 8;
                                                        this.left = rect.left;
                                                    }
                                                 }" class="relative">
                                                <div class="text-gray-800 font-medium text-sm leading-snug whitespace-normal max-w-[200px]">
                                                @php
                                                    $groupedBR = $booking->bookingRooms->groupBy('room_type_id');
                                                @endphp
                                                @foreach($groupedBR as $typeId => $typeRows)
                                                    <span>{{ $typeRows->first()->roomType?->display_name ?? '—' }}
                                                        @if($typeRows->count() > 1)<span class="text-hotel-gold font-bold">×{{ $typeRows->count() }}</span>@endif
                                                    </span>@if(!$loop->last)<span class="text-gray-300 mx-1">+</span>@endif
                                                @endforeach
                                                </div>
                                                <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => position())" @scroll.window="if(open) position()" @resize.window="if(open) position()"
                                                        class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                                    <i class="bi bi-door-closed"></i> View Rooms
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-transition x-cloak @click.outside="open = false"
                                                         :style="`top: ${top}px; left: ${left}px; transform: translateY(-100%);`"
                                                         class="fixed z-[9999] bg-white border border-gray-200 rounded-xl shadow-2xl p-3 w-56 text-xs">
                                                    <div class="font-bold text-gray-700 mb-2 flex items-center gap-1">
                                                        <i class="bi bi-building text-hotel-gold"></i> Assigned Rooms
                                                    </div>
                                                    @foreach($booking->bookingRooms as $br)
                                                        <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                                                            <span class="text-gray-600 font-medium">{{ $br->roomType?->display_name ?? '—' }}</span>
                                                            <span class="font-bold text-gray-900 flex items-center gap-1.5">
                                                                @if($br->room)
                                                                    Rm {{ $br->room->room_number }}
                                                                    @if($br->room->current_status === 'occupied')
                                                                        <button type="button" @click="$dispatch('open-confirm', { message: 'Mark Room {{ $br->room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $br->room->id }} }) })" title="Mark Vacated" class="text-amber-500 hover:text-amber-600 transition-colors bg-amber-50 px-1 py-0.5 rounded"><i class="bi bi-brush"></i></button>
                                                                    @endif
                                                                @else
                                                                    <span class="text-amber-600" title="Manage physically at front desk">TBA</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                </template>
                                            </div>
                                        @else
                                            <div class="text-gray-800 font-medium text-sm flex items-center gap-1.5">
                                                Room {{ $booking->room?->room_number ?? '—' }}
                                                @if($booking->room && $booking->room->current_status === 'occupied')
                                                    <button type="button" @click="$dispatch('open-confirm', { message: 'Mark Room {{ $booking->room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $booking->room->id }} }) })" title="Mark Vacated" class="text-amber-500 hover:text-amber-600 transition-colors bg-amber-50 px-1 py-0.5 rounded"><i class="bi bi-brush"></i></button>
                                                @endif
                                            </div>
                                        @endif
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
                                        @php $isMulti = $booking->bookingRooms->isNotEmpty(); @endphp
                                        @if($isMulti)
                                            <div x-data="{ 
                                                    open: false,
                                                    top: 0,
                                                    left: 0,
                                                    position() {
                                                        if (!this.$refs.btn) return;
                                                        const rect = this.$refs.btn.getBoundingClientRect();
                                                        this.top = rect.top - 8;
                                                        this.left = rect.left;
                                                    }
                                                 }" class="relative">
                                                <div class="text-gray-800 font-medium text-sm leading-snug whitespace-normal max-w-[200px]">
                                                @php
                                                    $groupedBR = $booking->bookingRooms->groupBy('room_type_id');
                                                @endphp
                                                @foreach($groupedBR as $typeId => $typeRows)
                                                    <span>{{ $typeRows->first()->roomType?->display_name ?? '—' }}
                                                        @if($typeRows->count() > 1)<span class="text-hotel-gold font-bold">×{{ $typeRows->count() }}</span>@endif
                                                    </span>@if(!$loop->last)<span class="text-gray-300 mx-1">+</span>@endif
                                                @endforeach
                                                </div>
                                                <button type="button" x-ref="btn" @click="open = !open; if(open) $nextTick(() => position())" @scroll.window="if(open) position()" @resize.window="if(open) position()"
                                                        class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                                    <i class="bi bi-door-closed"></i> View Rooms
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="open" x-transition x-cloak @click.outside="open = false"
                                                         :style="`top: ${top}px; left: ${left}px; transform: translateY(-100%);`"
                                                         class="fixed z-[9999] bg-white border border-gray-200 rounded-xl shadow-2xl p-3 w-56 text-xs">
                                                    <div class="font-bold text-gray-700 mb-2 flex items-center gap-1">
                                                        <i class="bi bi-building text-hotel-gold"></i> Assigned Rooms
                                                    </div>
                                                    @foreach($booking->bookingRooms as $br)
                                                        <div class="flex justify-between items-center py-1 border-b border-gray-100 last:border-0">
                                                            <span class="text-gray-600 font-medium">{{ $br->roomType?->display_name ?? '—' }}</span>
                                                            <span class="font-bold text-gray-900 flex items-center gap-1.5">
                                                                @if($br->room)
                                                                    Rm {{ $br->room->room_number }}
                                                                    @if($br->room->current_status === 'occupied')
                                                                        <button type="button" @click="$dispatch('open-confirm', { message: 'Mark Room {{ $br->room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $br->room->id }} }) })" title="Mark Vacated" class="text-amber-500 hover:text-amber-600 transition-colors bg-amber-50 px-1 py-0.5 rounded"><i class="bi bi-brush"></i></button>
                                                                    @endif
                                                                @else
                                                                    <span class="text-amber-600" title="Manage physically at front desk">TBA</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                </template>
                                            </div>
                                        @else
                                            <div class="text-gray-800 font-medium text-sm flex items-center gap-1.5">
                                                Room {{ $booking->room?->room_number ?? '—' }}
                                                @if($booking->room && $booking->room->current_status === 'occupied')
                                                    <button type="button" @click="$dispatch('open-confirm', { message: 'Mark Room {{ $booking->room->room_number }} as Vacated? This will notify Housekeeping to inspect and clean it immediately.', action: () => Livewire.dispatch('mark-cleaning', { roomId: {{ $booking->room->id }} }) })" title="Mark Vacated" class="text-amber-500 hover:text-amber-600 transition-colors bg-amber-50 px-1 py-0.5 rounded"><i class="bi bi-brush"></i></button>
                                                @endif
                                            </div>
                                        @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @php
                                        // Disable cancellation after 1 day past the expected arrival date
                                        $isTooLate = $booking->check_in_date->copy()->addDays(1)->isPast();
                                    @endphp
                                    @if($isTooLate)
                                        <button type="button" disabled
                                                class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-400 font-semibold px-3 py-1.5 rounded-lg text-xs border border-gray-200 cursor-not-allowed"
                                                title="Cancellation period expired">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    @else
                                        <button type="button" x-data @click.prevent="$dispatch('open-confirm', { message: 'Cancel no-show booking {{ $booking->referenceNumber() }} and release the room?', action: () => $wire.cancelNoShow({{ $booking->id }}) })"
                                                class="inline-flex items-center gap-1.5 bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-red-200">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    @endif
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

        </div>{{-- end TOP SECTION --}}

        {{-- ── BOTTOM SECTION: Today's Movement + Room Service Alerts ── --}}
        <div class="w-full space-y-5">

            {{-- Section header --}}
            <h2 class="font-playfair text-lg font-bold text-hotel-dark flex items-center gap-2">
                <i class="bi bi-people-fill text-teal-500"></i>
                Today's Overview
                <span class="text-xs font-normal text-gray-400 ml-1">{{ now()->format('M j') }}</span>
            </h2>

            {{-- Guest Movement & Alerts Cards Wrapper --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
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
                                <div class="text-right shrink-0 ml-2 max-w-[100px]">
                                    @if($booking->bookingRooms->isNotEmpty())
                                        <div class="flex flex-col items-end gap-0.5">
                                            @php
                                                $groupedIns = $booking->bookingRooms->groupBy('room_type_id');
                                            @endphp
                                            @foreach($groupedIns as $typeId => $typeRows)
                                                <div class="text-[0.65rem] font-bold text-gray-700 truncate w-full text-right" title="{{ $typeRows->count() }}x {{ $typeRows->first()->roomType?->display_name ?? 'Room' }}">
                                                    {{ $typeRows->count() }}x {{ strtok($typeRows->first()->roomType?->display_name ?? 'Room', ' ') }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs font-bold text-gray-700">Rm {{ $booking->room?->room_number ?? '—' }}</div>
                                    @endif
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
                                <div class="text-right shrink-0 ml-2 max-w-[100px]">
                                    @if($booking->bookingRooms->isNotEmpty())
                                        <div class="flex flex-col items-end gap-0.5">
                                            @foreach($booking->bookingRooms as $br)
                                                <div class="text-[0.65rem] font-bold text-gray-700 truncate w-full text-right" title="Rm {{ $br->room?->room_number ?? 'TBA' }}">
                                                    Rm {{ $br->room?->room_number ?? 'TBA' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-xs font-bold text-gray-700">Rm {{ $booking->room?->room_number ?? '—' }}</div>
                                    @endif
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
                {{-- Pending Housekeeping Request --}}
                @livewire('reception.housekeeping-requests-list')

            </div>

        </div>{{-- end BOTTOM SECTION --}}

    </div>{{-- end layout --}}

    {{-- =====================================================
         SETTLE BALANCE MODAL
         ===================================================== --}}
    <div x-data="{ 
            open: false, 
            settle_method: 'khqr_aba',
            bookingId: null,
            amountDue: 0,
            qrData: '',
            paymentRef: ''
        }"
         @settle-open.window="
            open = true;
            bookingId = $event.detail.bookingId;
            amountDue = parseFloat($event.detail.amount).toFixed(2);
            qrData = $event.detail.qr;
            paymentRef = '';
         "
         @close-settle-modal.window="open = false">
         
         <template x-teleport="body">
            <div wire:ignore
                 x-show="open"
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
                <span class="text-3xl font-bold text-red-600">$<span x-text="amountDue"></span></span>
            </div>

            <!-- QR Code -->
            <div class="flex justify-center mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <img :src="qrData" alt="QR Code" class="w-48 h-48 rounded-lg shadow-sm border border-gray-200 object-contain bg-white p-2">
            </div>

            <form wire:submit.prevent="markAsPaid(bookingId, amountDue, settle_method, paymentRef)">
                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-2 uppercase tracking-wide text-gray-500">Payment Method Received</label>
                    <select id="settle-payment-method" name="payment_method" x-model="settle_method" class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none bg-white font-medium text-gray-700">
                        <option value="khqr_aba">KHQR / ABA Static</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold mb-2 uppercase tracking-wide text-gray-500">Payment Reference 
                        <span x-show="settle_method !== 'cash'" class="text-red-500">*</span>
                    </label>
                    <input type="text" id="settle-payment-reference" x-model="paymentRef" 
                           x-bind:placeholder="settle_method === 'cash' ? 'e.g., Cash note (Optional)' : 'e.g., ABA Txn # (Required)'"
                           x-bind:required="settle_method !== 'cash'"
                           class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 transition-all outline-none">
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
        </template>
    </div>

{{-- ═══════════════════════════════════════════════════════════
     CHECK-OUT FOLIO MODAL
     Industry-standard folio-first layout:
       1. Stay summary (room lines, nights, paid status)
       2. Add incidental charges (optional)
       3. Payment settlement (only when charges exist)
     ══════════════════════════════════════════════════════════ --}}
<div x-data="{
        open: false,
        loadingData: false,

        // Booking identity
        bookingId:    null,
        reference:    '',
        guestName:    '',
        roomNumber:   '',
        rooms:        [],

        // URLs
        addChargeUrl: '',
        removeChargeUrl: '',

        // Folio data
        checkInDate:  '',
        checkOutDate: '',
        nights:       0,
        totalPrice:   0,
        totalPaid:    0,
        balanceDue:   0,
        folioLines:   [],  // [{name, qty, unitPrice, lineTotal}]

        // Early departure
        isEarly:           false,
        scheduledCheckout: '',

        // QR (for balance-due edge cases)
        qrDataUri: '',

        // Payment settlement
        paymentMethod: 'cash',
        paymentRef:    '',
        payRefError:   '',

        // Add-charge form state
        description: '',
        quantity:    1,
        amount:      '',
        selectedRoomIds: [],
        charges:     [],
        saving:      false,
        error:       '',

        // When a catalog item is selected, auto-fill description and amount.
        selectedCatalogId: '',
        onCatalogSelect(event) {
            if (!this.selectedCatalogId) { return; }
            const option = event.target.options[event.target.selectedIndex];
            if (option && option.dataset.amount) {
                this.description = option.text.split(' ($')[0];
                this.amount      = parseFloat(option.dataset.amount).toFixed(2);
            }
        },

        get chargesTotal() {
            return this.charges.reduce((sum, c) => sum + c.line_total, 0);
        },

        get totalDue() {
            return Math.max(0, this.balanceDue + this.chargesTotal);
        },

        get isSettled() {
            return this.totalPaid + 0.01 >= this.totalPrice;
        },

        async addCharge() {
            if (!this.description.trim() || !this.amount || !this.quantity) return;
            if (!this.selectedRoomIds || this.selectedRoomIds.length === 0) {
                this.error = 'Please select at least one room for this charge.';
                return;
            }

            this.saving = true;
            this.error  = '';
            try {
                let finalDesc = this.description;

                const res = await fetch(this.addChargeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        description: finalDesc,
                        quantity:    Number(this.quantity),
                        amount:      Number(this.amount),
                        room_ids:    this.selectedRoomIds,
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Failed');

                this.totalPrice = data.booking_total;
                this.qrDataUri  = data.qrDataUri || '';
                
                const newCharges = data.created_charges.map(c => {
                    const rmObj = this.rooms.find(r => r.id == c.room_id);
                    const rmStr = rmObj ? rmObj.number : c.room_id;
                    return {
                        id:          c.id,
                        description: c.description + (data.created_charges.length > 1 ? ` (Rm ${rmStr})` : ''),
                        quantity:    c.quantity,
                        unit_price:  Number(c.amount),
                        line_total:  Number(c.total_amount),
                    };
                });
                this.charges.push(...newCharges);

                this.description = '';
                this.quantity    = 1;
                this.amount      = '';
                this.selectedRoomIds = this.rooms.length === 1 ? [this.rooms[0].id] : [];
            } catch (e) {
                this.error = 'Network error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async removeCharge(chargeId) {
            if (!chargeId) return;
            if (!confirm('Are you sure you want to remove this charge?')) return;
            try {
                const url = this.removeChargeUrl.replace('__CHARGE_ID__', chargeId);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Failed to remove charge');
                
                this.totalPrice = data.booking_total;
                this.qrDataUri  = data.qrDataUri || '';
                this.charges = this.charges.filter(c => c.id !== chargeId);
            } catch(e) {
                alert(e.message);
            }
        },

        submitCheckout() {
            this.payRefError = '';
            if (this.totalDue > 0 && this.paymentMethod !== 'cash' && !this.paymentRef.trim()) {
                this.payRefError = 'A payment reference is required for KHQR / ABA transfers.';
                return;
            }
            this.$wire.checkOut(
                this.bookingId, 
                this.totalDue > 0 ? this.paymentMethod : null, 
                this.totalDue > 0 ? this.totalDue.toFixed(2) : null, 
                this.paymentRef
            );
        },
    }"
    @checkout-open.window="
        open          = true;
        bookingId     = $event.detail.bookingId;
        reference     = $event.detail.reference    ?? '';
        guestName     = $event.detail.guestName;
        roomNumber    = $event.detail.roomNumber   ?? '';
        rooms         = $event.detail.rooms  ?? [];
        addChargeUrl  = $event.detail.addChargeUrl;
        removeChargeUrl = $event.detail.removeChargeUrl;
        checkInDate   = $event.detail.checkInDate  ?? '';
        checkOutDate  = $event.detail.checkOutDate ?? '';
        nights        = parseInt($event.detail.nights ?? 0);
        totalPrice    = parseFloat($event.detail.totalPrice ?? 0);
        totalPaid     = parseFloat($event.detail.totalPaid  ?? 0);
        balanceDue    = parseFloat($event.detail.balanceDue ?? 0);
        folioLines    = $event.detail.folioLines   ?? [];
        isEarly           = $event.detail.isEarly ?? false;
        scheduledCheckout = $event.detail.scheduledCheckout ?? '';
        qrDataUri         = $event.detail.qrDataUri ?? '';
        paymentMethod = 'cash';
        paymentRef    = '';
        payRefError   = '';
        charges       = [];
        selectedRoomIds = rooms.length === 1 ? [rooms[0].id] : [];
        description   = '';
        quantity      = 1;
        amount        = '';
        error         = '';
        selectedCatalogId = '';
    "
    @close-checkout-modal.window="open = false"
    @open-checkout-modal.window="
        open          = true;
        bookingId     = $event.detail.bookingId;
        loadingData   = true;
        
        $wire.fetchCheckoutData(bookingId).then(data => {
            reference     = data.reference;
            guestName     = data.guestName;
            roomNumber    = data.roomNumber;
            rooms         = data.rooms || [];
            addChargeUrl  = data.addChargeUrl;
            removeChargeUrl = data.removeChargeUrl;
            checkInDate   = data.checkInDate;
            checkOutDate  = data.checkOutDate;
            nights        = data.nights;
            totalPrice    = data.totalPrice;
            totalPaid     = data.totalPaid;
            balanceDue    = data.balanceDue;
            folioLines    = data.folioLines;
            isEarly           = data.isEarly;
            scheduledCheckout = data.scheduledCheckout;
            qrDataUri         = data.qrDataUri;
            
            // Map existing charges from backend to Alpine array
            charges       = data.existingCharges || [];
            
            paymentMethod = 'cash';
            paymentRef    = '';
            payRefError   = '';
            selectedRoomIds = rooms.length === 1 ? [rooms[0].id] : [];
            description   = '';
            quantity      = 1;
            amount        = '';
            error         = '';
            loadingData   = false;
        });
    "
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[9000] flex items-center justify-center p-4"
    @keydown.escape.window="open = false">
             
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>

    {{-- Modal Panel --}}
    <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg flex flex-col"
         style="max-height: 92vh;"
         @click.stop>

        {{-- ── HEADER ─────────────────────────────────────────────────────── --}}
        <div class="flex items-start justify-between px-7 py-5 border-b border-gray-100 shrink-0">
            <div>
                <h2 class="font-playfair text-xl font-bold text-hotel-dark flex items-center gap-2">
                    <i class="bi bi-receipt text-amber-500"></i>
                    <span>Check-Out Folio</span>
                </h2>
                <p class="text-[0.8rem] text-gray-400 mt-0.5">
                    <span x-text="reference" class="font-mono font-semibold text-hotel-gold"></span>
                    <span class="mx-1.5 text-gray-300">·</span>
                    <span x-text="guestName"></span>
                </p>
            </div>
            <button @click="open = false"
                    class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition-colors shrink-0 ml-4">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- BODY (scrollable) --}}
        <div class="overflow-y-auto flex-1 px-7 py-5 space-y-5">

            {{-- ── LOADING OVERLAY ─────────────────────────────────────────── --}}
            <div x-show="loadingData" class="flex flex-col items-center justify-center py-10 space-y-3">
                <i class="bi bi-arrow-repeat animate-spin text-3xl text-amber-500"></i>
                <p class="text-sm font-semibold text-gray-500">Fetching latest folio...</p>
            </div>

            <div x-show="!loadingData" class="space-y-5">
            {{-- ── SECTION 1: Early-departure warning ──────────────────────── --}}
            <div x-show="isEarly"
                 class="flex items-start gap-3 bg-orange-50 border border-orange-200 rounded-2xl px-4 py-3 text-sm">
                <i class="bi bi-exclamation-triangle-fill text-orange-500 shrink-0 mt-0.5"></i>
                <div>
                    <p class="font-bold text-orange-700">Early Departure</p>
                    <p class="text-orange-600 text-xs mt-0.5">
                        This guest was scheduled to stay until
                        <strong x-text="scheduledCheckout"></strong>.
                        Proceed only if they have confirmed early check-out.
                    </p>
                </div>
            </div>

            {{-- ── SECTION 2: Stay Folio Summary ───────────────────────────── --}}
            <div class="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden">

                {{-- Folio header bar --}}
                <div class="flex items-center justify-between px-4 py-3 bg-gray-100 border-b border-gray-200">
                    <span class="text-[0.72rem] font-bold text-gray-500 uppercase tracking-widest">Stay Summary</span>
                    <span class="text-[0.72rem] text-gray-400">
                        <span x-text="checkInDate"></span>
                        <span class="mx-1">→</span>
                        <span x-text="checkOutDate"></span>
                        <span class="ml-1.5 font-semibold text-gray-600">(<span x-text="nights"></span>n)</span>
                    </span>
                </div>

                {{-- Per-room-type line items --}}
                <template x-for="(line, i) in folioLines" :key="i">
                    <div class="flex items-baseline justify-between px-4 py-2.5 border-b border-gray-100 last:border-0 text-[0.82rem]">
                        <div>
                            <span class="font-semibold text-gray-800" x-text="line.name"></span>
                            <span class="text-gray-400 ml-2">
                                <span x-text="line.qty > 1 ? '×' + line.qty + ' rooms · ' : ''"></span>
                                $<span x-text="line.unitPrice.toFixed(2)"></span>/night
                            </span>
                        </div>
                        <span class="font-bold text-gray-800" x-text="'$' + line.lineTotal.toFixed(2)"></span>
                    </div>
                </template>

                {{-- Folio totals --}}
                <div class="border-t border-gray-200 bg-white">
                    <div class="flex justify-between px-4 py-2.5 text-[0.82rem]">
                        <span class="text-gray-500">Room Total</span>
                        <span class="font-semibold text-gray-800" x-text="'$' + totalPrice.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5 text-[0.82rem] border-t border-gray-100">
                        <span class="flex items-center gap-1.5 text-emerald-700">
                            <i class="bi bi-check-circle-fill text-emerald-500 text-xs"></i>
                            Paid
                        </span>
                        <span class="font-semibold text-emerald-700" x-text="'−$' + totalPaid.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5 border-t border-gray-200" :class="balanceDue > 0 ? 'bg-red-50' : 'bg-emerald-50'">
                        <span class="font-bold text-sm" :class="balanceDue > 0 ? 'text-red-700' : 'text-emerald-700'">Balance Due</span>
                        <span class="font-extrabold text-sm" :class="balanceDue > 0 ? 'text-red-600' : 'text-emerald-600'">
                            <span x-show="balanceDue <= 0"><i class="bi bi-check-circle-fill text-emerald-500 mr-1"></i>Settled</span>
                            <span x-show="balanceDue > 0" x-text="'$' + balanceDue.toFixed(2)"></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 3: Add Incidental Charge ────────────────────────── --}}
            <div>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                    <i class="bi bi-plus-circle text-amber-500"></i>
                    Add Incidental Charge
                    <span class="text-gray-400 font-normal normal-case tracking-normal">— optional</span>
                </p>

                <div x-show="error"
                     class="mb-3 text-xs text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2 flex items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-red-400 shrink-0"></i>
                    <span x-text="error"></span>
                </div>

                <div x-show="rooms && rooms.length > 0" class="mb-3">
                    <label class="block text-[0.7rem] text-gray-500 font-bold uppercase tracking-wide mb-1">
                        Apply to Rooms <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="rm in rooms" :key="rm.id">
                            <label class="flex items-center gap-1.5 bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-xs cursor-pointer hover:bg-gray-50 transition-colors"
                                   :class="selectedRoomIds.includes(rm.id) ? 'border-amber-400 bg-amber-50/50' : ''">
                                <input type="checkbox" :value="rm.id" x-model="selectedRoomIds" class="rounded text-amber-500 focus:ring-amber-500 border-gray-300 w-3.5 h-3.5">
                                <span class="font-medium text-gray-700" x-text="'Rm ' + rm.number"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-5 gap-2 items-end">
                    {{-- Quick-select from incidental items catalog --}}
                    <div class="col-span-5 mb-1">
                        <label class="block text-[0.7rem] text-gray-500 font-bold uppercase tracking-wide mb-1">Quick Select</label>
                        <select x-model="selectedCatalogId" @change="onCatalogSelect($event)"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all bg-white text-gray-600">
                            <option value="">— Standard damage types —</option>
                            @foreach(\App\Models\IncidentalItem::active()->orderBy('name')->get() as $item)
                                <option value="{{ $item->id }}" data-amount="{{ $item->default_amount }}">
                                    {{ $item->name }} (${{ number_format($item->default_amount, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-5">
                        <label class="block text-[0.7rem] text-gray-500 font-bold uppercase tracking-wide mb-1">Description</label>
                        <input type="text" x-model="description"
                               placeholder="e.g. Broken lamp, Stained towel, Missing key…"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[0.7rem] text-gray-500 font-bold uppercase tracking-wide mb-1">Qty</label>
                        <input type="number" x-model="quantity" min="1" max="999"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[0.7rem] text-gray-500 font-bold uppercase tracking-wide mb-1">Unit Price (USD)</label>
                        <input type="number" step="0.01" x-model="amount" placeholder="0.00"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-[0.7rem] text-gray-500 font-bold uppercase tracking-wide mb-1">&nbsp;</label>
                        <button type="button" @click="addCharge()" :disabled="saving"
                                class="w-full flex items-center justify-center bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white font-bold px-3 py-2.5 rounded-xl text-sm transition-colors">
                            <i x-show="!saving" class="bi bi-plus-lg"></i>
                            <i x-show="saving"  class="bi bi-hourglass-split animate-spin"></i>
                        </button>
                    </div>
                </div>

                {{-- Charges list --}}
                <div x-show="charges.length > 0" class="mt-3">
                    <div class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden text-sm">
                        <template x-for="(c, i) in charges" :key="i">
                            <div class="flex items-center justify-between px-4 py-3 bg-white">
                                <div class="flex-1">
                                    <span class="font-semibold text-gray-800" x-text="c.description"></span>
                                    <span class="text-gray-400 text-xs ml-2" x-show="c.quantity > 1">
                                        × <span x-text="c.quantity"></span>
                                        @ $<span x-text="c.unit_price.toFixed(2)"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-amber-700" x-text="'$' + c.line_total.toFixed(2)"></span>
                                    <button type="button" @click="removeCharge(c.id)" x-show="c.id" title="Remove charge"
                                            class="text-gray-400 hover:text-red-500 transition-colors">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div class="flex justify-between px-4 py-3 bg-amber-50 font-bold text-sm">
                            <span class="text-gray-700">Charges Subtotal</span>
                            <span class="text-amber-700" x-text="'$' + chargesTotal.toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── SECTION 4: Payment Settlement (conditional) ───────────────── --}}
            <div x-show="totalDue > 0"
                 class="border border-red-200 bg-red-50 rounded-2xl p-4 space-y-3">

                <p class="text-sm font-bold text-red-700 flex items-center gap-2">
                    <i class="bi bi-wallet2"></i>
                    Collect Payment Before Check-Out
                </p>

                <div class="text-center py-1">
                    <span class="text-3xl font-extrabold text-red-600">$<span x-text="totalDue.toFixed(2)"></span> <span class="text-base font-semibold text-red-400">USD</span></span>
                    <span class="block text-xs text-gray-500 mt-0.5">Total due (room balance + incidentals)</span>
                </div>

                {{-- KHQR preview --}}
                <div x-show="paymentMethod !== 'cash' && qrDataUri"
                     class="flex justify-center bg-white p-3 rounded-xl border border-red-100">
                    <img :src="qrDataUri" alt="KHQR" class="w-36 h-36 object-contain rounded-lg">
                </div>

                <div>
                    <label class="block text-[0.7rem] font-bold text-gray-600 mb-1 uppercase tracking-wide">Payment Method</label>
                    <select x-model="paymentMethod"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all bg-white">
                        <option value="cash">Cash (USD / KHR)</option>
                        <option value="khqr_aba">KHQR / ABA Transfer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[0.7rem] font-bold text-gray-600 mb-1 uppercase tracking-wide">
                        Payment Reference
                        <span x-show="paymentMethod !== 'cash'" class="text-red-500">*</span>
                        <span x-show="paymentMethod === 'cash'" class="text-gray-400 font-normal normal-case tracking-normal">(optional)</span>
                    </label>
                    <input type="text" x-model="paymentRef"
                           :placeholder="paymentMethod === 'cash' ? 'e.g. Cash note' : 'ABA / KHQR transaction reference'"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 transition-all"
                           :class="payRefError ? 'border-red-400 ring-2 ring-red-100' : ''">
                    <p x-show="payRefError" class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i>
                        <span x-text="payRefError"></span>
                    </p>
                </div>
            </div>

            {{-- Settled confirmation (no charges and no balance) --}}
            <div x-show="totalDue <= 0 && chargesTotal <= 0"
                 class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 font-semibold">
                <i class="bi bi-check-circle-fill text-emerald-500"></i>
                Account fully settled — ready to check out.
            </div>
            
            </div>{{-- end not loading wrap --}}

        </div>{{-- end scrollable body --}}

        {{-- ── FOOTER ─────────────────────────────────────────────────────── --}}
        <div class="px-7 py-5 border-t border-gray-100 bg-gray-50 rounded-b-3xl shrink-0 flex items-center gap-3">
            <button type="button" @click="open = false"
                    class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-100 rounded-xl text-gray-700 font-semibold text-sm transition-colors">
                Cancel
            </button>

            <button type="button" @click="if(!loadingData) submitCheckout()"
                    :disabled="loadingData"
                    wire:loading.attr="disabled"
                    wire:target="checkOut"
                    class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-br from-amber-500 to-amber-700 hover:from-amber-600 hover:to-amber-800 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold px-6 py-2.5 rounded-xl text-sm shadow-md transition-all">
                <i class="bi bi-door-closed" wire:loading.remove wire:target="checkOut"></i>
                <i class="bi bi-hourglass-split animate-spin" wire:loading wire:target="checkOut"></i>
                <span wire:loading.remove wire:target="checkOut">Finalize Check-Out</span>
                <span wire:loading wire:target="checkOut">Processing...</span>
                <span x-show="charges.length > 0 && !loadingData"
                      class="ml-1 bg-white/20 text-white text-xs rounded-full px-2 py-0.5"
                      x-text="'+$' + chargesTotal.toFixed(2)"></span>
            </button>
        </div>
    </div>
</div>
</div>

@if(session('print_receipt'))
    <script>
        window.addEventListener('load', function() {
            window.open("{{ session('print_receipt') }}", "_blank");
        });
    </script>
@endif
