@extends('layouts.public')

@section('title', 'Admin Dashboard')

@section('content')

{{-- ==========================================
     DASHBOARD HEADER — with live clock + date
     ========================================== --}}
<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-10 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            {{-- Left: Title --}}
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-hotel-gold text-xs font-semibold uppercase tracking-widest bg-hotel-gold/10 border border-hotel-gold/30 px-3 py-1 rounded-full">
                        Admin Portal
                    </span>
                </div>
                <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold mb-1">Administrator Dashboard</h1>
                <p class="text-white/60 text-sm">Dara Meas Hotel &mdash; Hotel Overview &amp; Management</p>
            </div>
            {{-- Right: Widgets Row --}}
            <div class="flex flex-col sm:flex-row gap-4">
                {{-- Live Clock --}}
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-4 text-center min-w-[130px]">
                    <div class="text-hotel-gold text-[0.65rem] font-bold uppercase tracking-widest mb-1">
                        <i class="bi bi-clock mr-1"></i>Current Time
                    </div>
                    <div id="live-clock" class="font-playfair text-3xl font-bold text-white tabular-nums">--:--:--</div>
                    <div id="live-ampm" class="text-white/60 text-xs mt-0.5">--</div>
                </div>
                {{-- Gregorian Date --}}
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-4 text-center min-w-[155px]">
                    <div class="text-hotel-gold text-[0.65rem] font-bold uppercase tracking-widest mb-1">
                        <i class="bi bi-calendar3 mr-1"></i>Gregorian
                    </div>
                    <div id="greg-day" class="font-playfair text-3xl font-bold text-white leading-none">--</div>
                    <div id="greg-month-year" class="text-white/70 text-xs mt-1">-- ----</div>
                    <div id="greg-weekday" class="text-white/50 text-[0.65rem] uppercase tracking-wider mt-0.5">---</div>
                </div>
                {{-- Khmer Calendar --}}
                <div onclick="openKhmerCalendarModal()" class="bg-hotel-gold/20 backdrop-blur-sm border border-hotel-gold/40 rounded-2xl px-5 py-4 text-center min-w-[175px] cursor-pointer hover:bg-hotel-gold/30 hover:border-hotel-gold transition-all duration-200 group relative" title="Click to open full Khmer Lunar Calendar & Holidays">
                    <div class="text-hotel-gold text-[0.65rem] font-bold uppercase tracking-widest mb-1 flex items-center justify-center gap-1">
                        <i class="bi bi-moon-stars"></i>ប្រតិទិនខ្មែរ
                        <i class="bi bi-arrows-angle-expand opacity-0 group-hover:opacity-100 transition-opacity text-[0.6rem] ml-0.5"></i>
                    </div>
                    <div id="khmer-day" class="font-playfair text-3xl font-bold text-white leading-none">--</div>
                    <div id="khmer-month-year" class="text-hotel-gold/90 text-xs mt-1">-- ----</div>
                    <div id="khmer-lunar-day" class="text-white/50 text-[0.65rem] mt-0.5">---</div>
                </div>
                {{-- Weather --}}
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-4 text-center min-w-[130px]">
                    <div class="text-hotel-gold text-[0.65rem] font-bold uppercase tracking-widest mb-1">
                        <i class="bi bi-geo-alt mr-1"></i>Phnom Penh
                    </div>
                    <div id="weather-icon" class="text-3xl mb-1">🌤️</div>
                    <div id="weather-temp" class="font-playfair text-2xl font-bold text-white leading-none">--°C</div>
                    <div id="weather-desc" class="text-white/50 text-[0.65rem] mt-0.5">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    {{-- Flash Messages --}}
    @if (session('backup_success'))
        <div id="backup-alert-success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-4 mb-6 shadow-sm">
            <i class="bi bi-shield-check text-emerald-500 text-xl mt-0.5 shrink-0"></i>
            <div class="flex-1 text-sm font-medium">{{ session('backup_success') }}</div>
            <button onclick="document.getElementById('backup-alert-success').remove()" class="text-emerald-400 hover:text-emerald-600 text-lg leading-none ml-2">&times;</button>
        </div>
    @endif
    @if (session('backup_error'))
        <div id="backup-alert-error" class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-4 mb-6 shadow-sm">
            <i class="bi bi-exclamation-triangle text-red-500 text-xl mt-0.5 shrink-0"></i>
            <div class="flex-1 text-sm font-medium">{{ session('backup_error') }}</div>
            <button onclick="document.getElementById('backup-alert-error').remove()" class="text-red-400 hover:text-red-600 text-lg leading-none ml-2">&times;</button>
        </div>
    @endif

    {{-- ==========================================
         HOTEL STATISTICS
         ========================================== --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark border-b-2 border-gray-200 pb-3 mb-6 flex items-center">
        <i class="bi bi-graph-up text-blue-500 mr-3"></i>Hotel Statistics
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">

        {{-- Revenue --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2]">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-[1.4rem] mb-4"><i class="bi bi-currency-dollar"></i></div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">${{ number_format($monthlyRevenue, 2) }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Monthly Revenue</div>
        </div>

        {{-- Occupancy with % bar --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2]">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-[1.4rem] mb-4"><i class="bi bi-building"></i></div>
            <div class="flex items-end gap-2 mb-0.5">
                <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none">{{ $occupiedRooms }} / {{ $totalRooms }}</div>
                <span class="text-blue-500 font-bold text-sm mb-0.5">{{ $occupancyRate }}%</span>
            </div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider mb-3">Rooms Occupied</div>
            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="h-2 rounded-full" style="width: {{ $occupancyRate }}%; background: linear-gradient(to right, #3b82f6, #6366f1);"></div>
            </div>
            <div class="flex justify-between text-[0.65rem] text-gray-400 mt-1"><span>0%</span><span>Occupancy</span><span>100%</span></div>
        </div>

        {{-- Active Bookings --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2]">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-[1.4rem] mb-4"><i class="bi bi-calendar-check"></i></div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $activeBookings }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Active Bookings</div>
        </div>

        {{-- Registered Guests --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2]">
            <div class="w-12 h-12 rounded-xl bg-[#fff8ee] text-hotel-gold flex items-center justify-center text-[1.4rem] mb-4"><i class="bi bi-people"></i></div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $totalGuests }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Registered Guests</div>
        </div>

        {{-- Today Arrivals --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2]">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-500 flex items-center justify-center text-[1.4rem] mb-4"><i class="bi bi-box-arrow-in-right"></i></div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $todayArrivals }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Today's Arrivals</div>
        </div>

        {{-- Today Departures --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2]">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-[1.4rem] mb-4"><i class="bi bi-box-arrow-right"></i></div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $todayDepartures }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Today's Departures</div>
        </div>

        {{-- Rooms Available --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2]">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-[1.4rem] mb-4"><i class="bi bi-check-circle"></i></div>
            <div class="font-playfair text-3xl font-bold text-hotel-dark leading-none mb-1">{{ $availableRooms }}</div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider">Rooms Available</div>
        </div>

        {{-- System Backup --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 transition-transform hover:-translate-y-1 border border-[#f0ebe2] flex flex-col">
            @php
                $iconClass  = match($backupStatus) { 'healthy'=>'bi-shield-check text-emerald-600','outdated'=>'bi-shield-exclamation text-amber-500','no_backup'=>'bi-shield-x text-red-500',default=>'bi-shield text-gray-400' };
                $bgClass    = match($backupStatus) { 'healthy'=>'bg-emerald-50','outdated'=>'bg-amber-50','no_backup'=>'bg-red-50',default=>'bg-gray-50' };
                $badgeClass = match($backupStatus) { 'healthy'=>'bg-emerald-100 text-emerald-700','outdated'=>'bg-amber-100 text-amber-700','no_backup'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-600' };
                $badgeLabel = match($backupStatus) { 'healthy'=>'● Secured','outdated'=>'⚠ Outdated','no_backup'=>'✕ No Backup',default=>'? Unknown' };
                $adminId    = auth('admin')->id();
                $onCooldown = cache()->has("admin_manual_backup_{$adminId}");
                $expiresAt  = $onCooldown ? (cache()->get("admin_manual_backup_{$adminId}") ?? now()->timestamp) : now()->timestamp;
                $cooldownSecs = $onCooldown ? max(0, $expiresAt - now()->timestamp) : 0;
                $cooldownMins = $onCooldown ? (int) ceil($cooldownSecs / 60) : 0;
            @endphp
            <div class="w-12 h-12 rounded-xl {{ $bgClass }} flex items-center justify-center text-[1.4rem] mb-4"><i class="bi {{ $iconClass }}"></i></div>
            <div class="flex items-center gap-2 mb-1"><span class="text-sm font-bold px-2 py-0.5 rounded-full {{ $badgeClass }}">{{ $badgeLabel }}</span></div>
            <div class="text-[0.85rem] text-gray-500 font-semibold uppercase tracking-wider mb-2">System Backup</div>
            <div class="text-xs text-gray-400 leading-snug mb-4">
                @if ($lastBackupTime) Last backup:<br><span class="text-gray-600 font-medium">{{ $lastBackupTime->isToday() ? 'Today' : $lastBackupTime->format('M d, Y') }} at {{ $lastBackupTime->format('g:i A') }}</span>
                @else No backup on record yet. @endif
            </div>
            <div class="mt-auto">
                @if ($onCooldown)
                    <div class="w-full flex items-center justify-center gap-2 text-xs text-gray-400 bg-gray-100 rounded-lg px-3 py-2 cursor-not-allowed"><i class="bi bi-hourglass-split"></i> Next in {{ $cooldownMins }} min{{ $cooldownMins !== 1 ? 's' : '' }}</div>
                @else
                    <form method="POST" action="{{ route('admin.backup.run') }}" onsubmit="return confirm('Run a manual backup now?')">@csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 text-xs font-semibold bg-hotel-dark text-white rounded-lg px-3 py-2 hover:bg-hotel-accent transition-colors"><i class="bi bi-cloud-arrow-up"></i> Backup Now</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- ==========================================
         TODAY'S GUEST MOVEMENT
         ========================================== --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark border-b-2 border-gray-200 pb-3 mb-6 flex items-center">
        <i class="bi bi-people text-teal-500 mr-3"></i>Today's Guest Movement
        <span class="ml-3 text-sm font-normal text-gray-400">{{ now()->format('l, F j, Y') }}</span>
    </h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
        {{-- Arrivals List --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] border border-[#f0ebe2] overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-teal-50/50">
                <div class="w-9 h-9 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center"><i class="bi bi-box-arrow-in-right text-lg"></i></div>
                <div>
                    <div class="font-semibold text-hotel-dark text-sm">Check-Ins Today</div>
                    <div class="text-teal-600 text-xs font-bold">{{ $todayArrivals }} guest{{ $todayArrivals !== 1 ? 's' : '' }} arriving</div>
                </div>
            </div>
            @if($arrivalsToday->count() > 0)
                <ul class="divide-y divide-gray-50">
                    @foreach($arrivalsToday as $booking)
                    <li class="flex items-center justify-between px-6 py-3.5 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($booking->guest?->full_name ?? 'G', 0, 1)) }}</div>
                            <div>
                                <div class="text-sm font-semibold text-hotel-dark">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                <div class="text-xs text-gray-400">{{ $booking->referenceNumber() }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-hotel-dark">Room {{ $booking->room?->room_number ?? '-' }}</div>
                            <div class="text-[0.7rem] text-gray-400">{{ $booking->room?->roomType?->display_name ?? '' }}</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-8 text-center text-gray-400">
                    <i class="bi bi-calendar-x text-3xl block mb-2 text-gray-300"></i>
                    <p class="text-sm">No arrivals scheduled for today.</p>
                </div>
            @endif
        </div>
        {{-- Departures List --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] border border-[#f0ebe2] overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-red-50/50">
                <div class="w-9 h-9 rounded-xl bg-red-100 text-red-500 flex items-center justify-center"><i class="bi bi-box-arrow-right text-lg"></i></div>
                <div>
                    <div class="font-semibold text-hotel-dark text-sm">Check-Outs Today</div>
                    <div class="text-red-500 text-xs font-bold">{{ $todayDepartures }} guest{{ $todayDepartures !== 1 ? 's' : '' }} departing</div>
                </div>
            </div>
            @if($departuresToday->count() > 0)
                <ul class="divide-y divide-gray-50">
                    @foreach($departuresToday as $booking)
                    <li class="flex items-center justify-between px-6 py-3.5 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-bold shrink-0">{{ strtoupper(substr($booking->guest?->full_name ?? 'G', 0, 1)) }}</div>
                            <div>
                                <div class="text-sm font-semibold text-hotel-dark">{{ $booking->guest?->full_name ?? 'Walk-in Guest' }}</div>
                                <div class="text-xs text-gray-400">{{ $booking->referenceNumber() }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-hotel-dark">Room {{ $booking->room?->room_number ?? '-' }}</div>
                            <div class="text-[0.7rem] text-gray-400">{{ $booking->room?->roomType?->display_name ?? '' }}</div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @else
                <div class="px-6 py-8 text-center text-gray-400">
                    <i class="bi bi-calendar-x text-3xl block mb-2 text-gray-300"></i>
                    <p class="text-sm">No departures scheduled for today.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ==========================================
         ANALYTICS CHARTS
         ========================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b-2 border-gray-200 pb-3 mb-6">
        <h2 class="font-playfair text-2xl font-bold text-hotel-dark flex items-center">
            <i class="bi bi-bar-chart-line text-blue-500 mr-3"></i>Financial &amp; Booking Analytics
        </h2>
        {{-- Period Quick-Select --}}
        <div class="flex flex-wrap items-center gap-2" id="analytics-period-controls">
            <button data-period="7"   class="analytics-preset-btn active" id="preset-7">7D</button>
            <button data-period="30"  class="analytics-preset-btn"        id="preset-30">30D</button>
            <button data-period="90"  class="analytics-preset-btn"        id="preset-90">90D</button>
            <button data-period="365" class="analytics-preset-btn"        id="preset-365">1Y</button>
            <button data-period="all" class="analytics-preset-btn"        id="preset-all">All</button>
            <button onclick="document.getElementById('analytics-custom-range').classList.toggle('hidden')" class="analytics-preset-btn" id="preset-custom">
                <i class="bi bi-calendar-range"></i> Custom
            </button>
        </div>
    </div>

    {{-- Custom date range picker (hidden by default) --}}
    <div id="analytics-custom-range" class="hidden bg-[#fdfaf6] border border-[#f0ebe2] rounded-2xl p-4 mb-6 flex flex-wrap items-end gap-4">
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">From</label>
            <input type="date" id="custom-start" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">To</label>
            <input type="date" id="custom-end" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-hotel-gold focus:ring-1 focus:ring-hotel-gold">
        </div>
        <button onclick="applyCustomRange()" class="bg-hotel-dark text-white text-sm font-semibold px-5 py-2 rounded-xl hover:bg-hotel-accent transition-colors">
            <i class="bi bi-funnel mr-1"></i> Apply
        </button>
    </div>

    {{-- KPI summary pills for the selected period --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <div class="bg-white border border-[#f0ebe2] rounded-xl px-5 py-3 flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center"><i class="bi bi-currency-dollar"></i></div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Period Revenue</div>
                <div id="kpi-revenue" class="font-playfair text-xl font-bold text-hotel-dark">—</div>
            </div>
        </div>
        <div class="bg-white border border-[#f0ebe2] rounded-xl px-5 py-3 flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total Bookings</div>
                <div id="kpi-bookings" class="font-playfair text-xl font-bold text-hotel-dark">—</div>
            </div>
        </div>
        <div class="bg-white border border-[#f0ebe2] rounded-xl px-5 py-3 flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Completed Stays</div>
                <div id="kpi-completed" class="font-playfair text-xl font-bold text-hotel-dark">—</div>
            </div>
        </div>
        <div class="ml-auto text-xs text-gray-400 self-center italic" id="analytics-period-label">Loading…</div>
    </div>

    {{-- Chart Grid: 2×2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">

        {{-- Revenue over time --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 border border-[#f0ebe2] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-semibold text-base text-hotel-dark">Revenue Over Time</h5>
                <span class="text-xs text-gray-400 bg-gray-50 border border-gray-100 rounded-full px-2 py-0.5">Paid only</span>
            </div>
            <div id="chart-revenue" style="height:240px;"></div>
        </div>

        {{-- Booking Volume over time --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 border border-[#f0ebe2] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-semibold text-base text-hotel-dark">Booking Volume</h5>
                <span class="text-xs text-gray-400 bg-gray-50 border border-gray-100 rounded-full px-2 py-0.5">All statuses</span>
            </div>
            <div id="chart-booking-volume" style="height:240px;"></div>
        </div>

        {{-- Revenue by Room Type --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 border border-[#f0ebe2] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-semibold text-base text-hotel-dark">Revenue by Room Type</h5>
                <span class="text-xs text-gray-400 bg-gray-50 border border-gray-100 rounded-full px-2 py-0.5">Paid revenue</span>
            </div>
            <div id="chart-revenue-by-type" style="height:240px;"></div>
        </div>

        {{-- Booking Status Distribution --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 border border-[#f0ebe2] flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h5 class="font-semibold text-base text-hotel-dark">Booking Status Distribution</h5>
                <span class="text-xs text-gray-400 bg-gray-50 border border-gray-100 rounded-full px-2 py-0.5">For period</span>
            </div>
            <div id="chart-booking-status" style="height:240px;"></div>
        </div>

    </div>

    {{-- ==========================================
         QUICK ACTIONS
         ========================================== --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark border-b-2 border-gray-200 pb-3 mb-6 flex items-center">
        <i class="bi bi-grid text-gray-400 mr-3"></i>Quick Actions
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="{{ route('admin.rooms.index') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-door-open text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">Manage Rooms</h5>
            <p class="text-[0.85rem] text-gray-500">Add, edit, or remove hotel rooms.</p>
        </a>
        <a href="{{ route('admin.room-types.index') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-tag text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">Room Types</h5>
            <p class="text-[0.85rem] text-gray-500">Set pricing, capacity, and categories.</p>
        </a>
        <a href="{{ route('admin.bookings.index') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-journal-bookmark text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">All Bookings</h5>
            <p class="text-[0.85rem] text-gray-500">View and manage all reservations.</p>
        </a>
        <a href="{{ route('admin.staff.index') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-person-badge text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">Manage Staff</h5>
            <p class="text-[0.85rem] text-gray-500">Add or remove Receptionists.</p>
        </a>
        <a href="{{ url('reception/dashboard') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-reception-4 text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">Reception Desk</h5>
            <p class="text-[0.85rem] text-gray-500">Go to front desk operations.</p>
        </a>
        <a href="{{ route('admin.payment-gateways.index') }}" class="block bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-8 text-center transition-all hover:-translate-y-1 hover:bg-[#fdfaf6] hover:border-hotel-gold border border-[#f0ebe2] text-hotel-dark group">
            <i class="bi bi-credit-card-2-front text-4xl text-hotel-gold block mb-4 group-hover:scale-110 transition-transform"></i>
            <h5 class="font-semibold text-lg mb-2">Payment Gateways</h5>
            <p class="text-[0.85rem] text-gray-500">Enable/disable payment options.</p>
        </a>
    </div>

</div>

{{-- ==========================================
     KHMER LUNAR CALENDAR MODAL (Interactive)
     ========================================== --}}
<div id="khmer-calendar-modal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-black/65 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-300" onclick="closeKhmerCalendarOnBackdrop(event)">
    <div id="khmer-calendar-modal-content" class="bg-white dark:bg-[#131c28] border border-gray-200 dark:border-gray-800 rounded-3xl shadow-2xl overflow-hidden max-w-6xl w-full max-h-[92vh] flex flex-col transition-all transform scale-95 duration-300">
        
        {{-- Modal Top Banner --}}
        <div class="bg-gradient-to-r from-hotel-dark via-[#1a2636] to-hotel-dark px-6 py-4 flex items-center justify-between text-white border-b border-hotel-gold/30 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-hotel-gold/20 border border-hotel-gold/40 flex items-center justify-center text-hotel-gold text-xl">
                    <i class="bi bi-moon-stars-fill"></i>
                </div>
                <div>
                    <h3 class="font-playfair text-xl md:text-2xl font-bold leading-tight flex items-center gap-2">
                        ប្រតិទិនចន្ទគតិខ្មែរ <span class="text-xs font-sans font-normal text-hotel-gold/80 bg-hotel-gold/10 px-2.5 py-0.5 rounded-full border border-hotel-gold/30 hidden sm:inline-block">Khmer Lunar Calendar</span>
                    </h3>
                    <p class="text-white/60 text-xs">Dara Meas Hotel &mdash; Traditional Observances &amp; Cambodian Public Holidays</p>
                </div>
            </div>
            <button onclick="closeKhmerCalendarModal()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition-colors">
                <i class="bi bi-x-lg text-base"></i>
            </button>
        </div>

        {{-- Month Navigation & Year Info Sub-header --}}
        <div class="px-6 py-3.5 bg-[#fdfaf6] dark:bg-[#1a2534] border-b border-[#f0ebe2] dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0">
            <div id="khmer-modal-year-details" class="font-semibold text-hotel-dark dark:text-white text-sm md:text-base flex items-center gap-2">
                <i class="bi bi-calendar-event text-hotel-gold"></i>
                <span>---</span>
            </div>
            <div class="flex items-center justify-between sm:justify-end gap-2">
                <button onclick="changeKhmerCalendarMonth(-1)" class="px-3 py-1.5 rounded-xl bg-white dark:bg-[#233144] border border-gray-200 dark:border-gray-700 hover:border-hotel-gold hover:bg-hotel-gold/10 text-hotel-dark dark:text-white font-medium transition flex items-center gap-1 text-xs md:text-sm shadow-sm">
                    <i class="bi bi-chevron-left"></i> <span class="hidden sm:inline">មុន (Prev)</span>
                </button>
                <span id="khmer-modal-month-name" class="font-playfair font-bold text-lg md:text-xl px-4 text-hotel-dark dark:text-white min-w-[190px] text-center">
                    -- ----
                </span>
                <button onclick="changeKhmerCalendarMonth(1)" class="px-3 py-1.5 rounded-xl bg-white dark:bg-[#233144] border border-gray-200 dark:border-gray-700 hover:border-hotel-gold hover:bg-hotel-gold/10 text-hotel-dark dark:text-white font-medium transition flex items-center gap-1 text-xs md:text-sm shadow-sm">
                    <span class="hidden sm:inline">(Next) បន្ទាប់</span> <i class="bi bi-chevron-right"></i>
                </button>
                <button onclick="resetKhmerCalendarMonth()" class="ml-1 px-3.5 py-1.5 rounded-xl bg-hotel-gold text-white font-semibold text-xs uppercase tracking-wider hover:bg-hotel-gold/90 transition shadow-sm">
                    ថ្ងៃនេះ (Today)
                </button>
            </div>
        </div>

        {{-- Main Content Grid & Sidebar --}}
        <div class="flex-1 overflow-y-auto p-4 md:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 bg-[#faf8f5] dark:bg-[#0f1722]">
            
            {{-- Calendar Grid (Left 2 Columns) --}}
            <div class="lg:col-span-2">
                {{-- Weekday Headers --}}
                <div class="grid grid-cols-7 gap-1.5 md:gap-2 mb-2 text-center" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr));">
                    <div class="py-2 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl text-red-600 dark:text-red-400 font-bold text-xs md:text-sm">អាទិ (Sun)</div>
                    <div class="py-2 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-100/80 dark:border-amber-900/20 rounded-xl text-amber-700 dark:text-amber-300 font-bold text-xs md:text-sm">ចន្ទ (Mon)</div>
                    <div class="py-2 bg-purple-50/70 dark:bg-purple-950/30 border border-purple-100/80 dark:border-purple-900/20 rounded-xl text-purple-700 dark:text-purple-300 font-bold text-xs md:text-sm">អង្គ (Tue)</div>
                    <div class="py-2 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100/80 dark:border-emerald-900/20 rounded-xl text-emerald-700 dark:text-emerald-300 font-bold text-xs md:text-sm">ពុធ (Wed)</div>
                    <div class="py-2 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100/80 dark:border-blue-900/20 rounded-xl text-blue-700 dark:text-blue-300 font-bold text-xs md:text-sm">ព្រហ (Thu)</div>
                    <div class="py-2 bg-sky-50/70 dark:bg-sky-950/30 border border-sky-100/80 dark:border-sky-900/20 rounded-xl text-sky-700 dark:text-sky-300 font-bold text-xs md:text-sm">សុក្រ (Fri)</div>
                    <div class="py-2 bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100/80 dark:border-indigo-900/20 rounded-xl text-indigo-700 dark:text-indigo-300 font-bold text-xs md:text-sm">សៅរ៍ (Sat)</div>
                </div>
                {{-- Dynamic Day Cells Grid --}}
                <div id="khmer-calendar-grid" class="grid grid-cols-7 gap-1.5 md:gap-2" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); grid-auto-rows: auto;">
                    {{-- populated via JS --}}
                </div>
                {{-- Legend --}}
                <div class="flex flex-wrap items-center gap-4 mt-3 pt-3 border-t border-gray-200/60 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-hotel-gold/20 border border-hotel-gold inline-block"></span> <span>ថ្ងៃនេះ (Today)</span></div>
                    <div class="flex items-center gap-1.5"><span class="text-sm">🙏</span> <span>ថ្ងៃសីល (Buddhist Holy Day / Sila)</span></div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-red-100 border border-red-300 inline-block"></span> <span>ថ្ងៃបុណ្យជាតិ (Public Holiday)</span></div>
                    <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-blue-100 border border-blue-300 inline-block"></span> <span>ពិធីបុណ្យប្រពៃណី និងទិវាផ្សេងៗ (Observance)</span></div>
                </div>
            </div>

            {{-- Sidebar Details (Right Column) --}}
            <div class="lg:col-span-1 flex flex-col gap-4">
                {{-- Today / Selected Day Details Card --}}
                <div class="bg-gradient-to-br from-hotel-dark via-[#1c2636] to-[#243348] text-white rounded-2xl p-5 border border-hotel-gold/40 shadow-lg relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 text-hotel-gold/10 text-8xl pointer-events-none"><i class="bi bi-moon-stars"></i></div>
                    <div class="flex items-center justify-between border-b border-white/10 pb-2.5 mb-3">
                        <span id="khmer-modal-selected-label" class="text-hotel-gold text-xs font-bold uppercase tracking-widest flex items-center gap-1.5">
                            <i class="bi bi-calendar-check"></i> ថ្ងៃនេះ (Today's Date)
                        </span>
                        <span id="khmer-modal-selected-greg" class="text-xs bg-white/10 px-2 py-0.5 rounded-md font-mono text-white/80"></span>
                    </div>
                    <div id="khmer-modal-selected-full-text" class="text-sm md:text-base leading-relaxed text-hotel-gold font-medium mb-3 min-h-[50px]">
                        Loading lunar date details...
                    </div>
                    <div id="khmer-modal-sila-banner" class="hidden mt-2 bg-hotel-gold/20 border border-hotel-gold/50 rounded-xl px-3 py-2 text-xs font-bold text-hotel-gold flex items-center gap-2">
                        <span class="text-base">🙏</span> ថ្ងៃនេះជាថ្ងៃឧបោសថសីល (Sacred Sila Day)
                    </div>
                    <div id="khmer-modal-holiday-banner" class="hidden mt-2 bg-red-500/20 border border-red-500/50 rounded-xl px-3 py-2 text-xs font-bold text-red-300 flex items-center gap-2">
                        <i class="bi bi-flag-fill text-red-400"></i> <span id="khmer-modal-holiday-banner-text">---</span>
                    </div>
                </div>

                {{-- Monthly Holidays & Observances Card --}}
                <div class="bg-white dark:bg-[#1a2534] rounded-2xl p-5 border border-[#f0ebe2] dark:border-gray-800 shadow-sm flex-1 flex flex-col min-h-[240px]">
                    <h4 class="font-semibold text-hotel-dark dark:text-white mb-3 pb-2.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between text-sm md:text-base">
                        <span class="flex items-center gap-2"><i class="bi bi-flag-fill text-red-500"></i> បុណ្យជាតិ និងព្រឹត្តិការណ៍</span>
                        <span id="khmer-modal-holiday-count" class="text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">0</span>
                    </h4>
                    <ul id="khmer-modal-holidays-list" class="space-y-2.5 text-xs text-gray-600 dark:text-gray-300 flex-1 overflow-y-auto pr-1">
                        <li class="text-gray-400 italic py-4 text-center">Loading holidays...</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
.analytics-preset-btn {
    padding: 0.3rem 0.85rem;
    border-radius: 9999px;
    font-size: 0.78rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.15s ease;
}
.analytics-preset-btn:hover {
    border-color: #c8a96e;
    color: #c8a96e;
    background: #fdfaf6;
}
.analytics-preset-btn.active {
    background: #1a2636;
    border-color: #1a2636;
    color: #fff;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ANALYTICS_URL = '{{ route("admin.dashboard.analytics") }}';
    const GOLD  = '#c8a96e';
    const DARK  = '#1a2636';
    const COLORS = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#6b7280','#14b8a6','#f97316'];

    // ── ApexChart instances ────────────────────────────────────
    const defaultOpts = {
        chart: { toolbar: { show: false }, fontFamily: 'Inter, sans-serif', animations: { easing: 'easeinout', speed: 600 } },
        noData: { text: 'No data for this period', style: { color: '#9ca3af', fontSize: '13px' } },
    };

    const revenueChart = new ApexCharts(document.getElementById('chart-revenue'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'area', height: 240 },
        series: [{ name: 'Revenue ($)', data: [] }],
        xaxis: { categories: [], labels: { style: { fontSize: '11px' }, rotate: -35 }, tickAmount: 8 },
        yaxis: { labels: { formatter: v => '$' + v.toLocaleString() } },
        colors: [GOLD],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02 } },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: v => '$' + parseFloat(v).toFixed(2) } },
        grid: { borderColor: '#f3f4f6' },
    });
    revenueChart.render();

    const volumeChart = new ApexCharts(document.getElementById('chart-booking-volume'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'bar', height: 240 },
        series: [{ name: 'Bookings', data: [] }],
        xaxis: { categories: [], labels: { style: { fontSize: '11px' }, rotate: -35 }, tickAmount: 8 },
        yaxis: { labels: { formatter: v => Math.round(v) } },
        colors: ['#6366f1'],
        plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#f3f4f6' },
    });
    volumeChart.render();

    const typeChart = new ApexCharts(document.getElementById('chart-revenue-by-type'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'bar', height: 240 },
        series: [{ name: 'Revenue ($)', data: [] }],
        xaxis: { categories: [] },
        yaxis: { labels: { formatter: v => '$' + v.toLocaleString() } },
        colors: [GOLD, '#10b981', '#3b82f6'],
        plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '55%', distributed: true } },
        dataLabels: { enabled: true, formatter: v => '$' + parseFloat(v).toFixed(0) },
        legend: { show: false },
        tooltip: { y: { formatter: v => '$' + parseFloat(v).toFixed(2) } },
        grid: { borderColor: '#f3f4f6' },
    });
    typeChart.render();

    const statusChart = new ApexCharts(document.getElementById('chart-booking-status'), {
        ...defaultOpts,
        chart: { ...defaultOpts.chart, type: 'donut', height: 240 },
        series: [],
        labels: [],
        colors: COLORS,
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total', formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b,0) } } } } },
        dataLabels: { enabled: false },
        legend: { position: 'right', fontSize: '12px' },
    });
    statusChart.render();

    // ── KPI updater ────────────────────────────────────────────
    function updateKPIs(summary, label) {
        document.getElementById('kpi-revenue').textContent   = '$' + parseFloat(summary.total_revenue).toFixed(2);
        document.getElementById('kpi-bookings').textContent  = summary.total_bookings;
        document.getElementById('kpi-completed').textContent = summary.completed_bookings;
        document.getElementById('analytics-period-label').textContent = label;
    }

    // ── Main data fetch + chart update ─────────────────────────
    function loadAnalytics(startDate, endDate) {
        // Show loading state
        document.getElementById('kpi-revenue').textContent   = '…';
        document.getElementById('kpi-bookings').textContent  = '…';
        document.getElementById('kpi-completed').textContent = '…';

        let url = ANALYTICS_URL;
        if (startDate) url += '?start_date=' + startDate + '&end_date=' + endDate;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                // Revenue area chart
                revenueChart.updateOptions({
                    series: [{ name: 'Revenue ($)', data: data.revenue.map(d => d.value) }],
                    xaxis:  { categories: data.revenue.map(d => d.label) },
                });

                // Booking volume bar chart
                volumeChart.updateOptions({
                    series: [{ name: 'Bookings', data: data.bookingVolume.map(d => d.value) }],
                    xaxis:  { categories: data.bookingVolume.map(d => d.label) },
                });

                // Revenue by room type horizontal bar
                typeChart.updateOptions({
                    series: [{ name: 'Revenue ($)', data: data.revenueByType.map(d => parseFloat(d.value)) }],
                    xaxis:  { categories: data.revenueByType.map(d => d.label) },
                });

                // Status distribution donut
                if (data.bookingStatuses.length > 0) {
                    statusChart.updateOptions({
                        series: data.bookingStatuses.map(d => d.value),
                        labels: data.bookingStatuses.map(d => d.label),
                    });
                } else {
                    statusChart.updateOptions({ series: [], labels: [] });
                }

                updateKPIs(data.summary, data.period.label);
            })
            .catch(() => {
                document.getElementById('analytics-period-label').textContent = 'Error loading data';
            });
    }

    // ── Preset buttons ─────────────────────────────────────────
    function setActivePreset(id) {
        document.querySelectorAll('.analytics-preset-btn').forEach(b => b.classList.remove('active'));
        const el = document.getElementById(id);
        if (el) el.classList.add('active');
    }

    document.querySelectorAll('.analytics-preset-btn[data-period]').forEach(btn => {
        btn.addEventListener('click', function() {
            const period = this.dataset.period;
            setActivePreset(this.id);
            document.getElementById('analytics-custom-range').classList.add('hidden');

            if (period === 'all') {
                loadAnalytics(null, null);
                return;
            }
            const end   = new Date();
            const start = new Date();
            start.setDate(start.getDate() - (parseInt(period) - 1));
            loadAnalytics(fmtDate(start), fmtDate(end));
        });
    });

    function fmtDate(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }

    window.applyCustomRange = function() {
        const s = document.getElementById('custom-start').value;
        const e = document.getElementById('custom-end').value;
        if (!s || !e) { alert('Please select both a start and end date.'); return; }
        setActivePreset('preset-custom');
        loadAnalytics(s, e);
    };

    // Seed custom date inputs with sensible defaults
    const today = new Date();
    const monthAgo = new Date(); monthAgo.setDate(today.getDate() - 29);
    document.getElementById('custom-end').value   = fmtDate(today);
    document.getElementById('custom-start').value = fmtDate(monthAgo);

    // ── Boot: load default 7-day view ──────────────────────────
    const initEnd   = new Date();
    const initStart = new Date();
    initStart.setDate(initStart.getDate() - 6);
    loadAnalytics(fmtDate(initStart), fmtDate(initEnd));


    // ── Live Clock ─────────────────────────────────────────────
    function updateClock() {
        const n = new Date();
        const h = String(n.getHours() % 12 || 12).padStart(2,'0');
        const m = String(n.getMinutes()).padStart(2,'0');
        const s = String(n.getSeconds()).padStart(2,'0');
        document.getElementById('live-clock').textContent = h+':'+m+':'+s;
        document.getElementById('live-ampm').textContent  = n.getHours() >= 12 ? 'PM' : 'AM';
    }
    updateClock(); setInterval(updateClock, 1000);

    // ── Gregorian Date ─────────────────────────────────────────
    const now = new Date();
    const wdays  = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('greg-day').textContent        = now.getDate();
    document.getElementById('greg-month-year').textContent = months[now.getMonth()]+' '+now.getFullYear();
    document.getElementById('greg-weekday').textContent    = wdays[now.getDay()];

    // ── Khmer Lunar Calendar & Interactive Modal Engine ─────────
    const khmerMonths = ['មិគសិរ','បុស្ស','មាឃ','ផល្គុន','ចេត្រ','វិសាខ','ជេស្ឋ','អាសាឍ','ស្រាពណ៍','ភទ្របទ','អស្សុជ','កត្តិក'];
    const khmerMonthsEn = ['Migasira','Pussa','Meakha','Phalguna','Cetra','Visakha','Jestha','Asadha','Sravana','Bhadrapada','Asvina','Kattika'];
    const khmerNums   = ['០','១','២','៣','៤','៥','៦','៧','៨','៩'];
    const khmerZodiacs = ['ជូត (Rat)','ឆ្លូវ (Ox)','ខាល (Tiger)','ថោះ (Rabbit)','រោង (Dragon)','ម្សាញ់ (Snake)','មមី (Horse)','មមែ (Goat)','វក (Monkey)','រកា (Rooster)','ច (Dog)','កុរ (Pig)'];
    const khmerWeekdaysFull = ['ថ្ងៃអាទិត្យ','ថ្ងៃចន្ទ','ថ្ងៃអង្គារ','ថ្ងៃពុធ','ថ្ងៃព្រហស្បតិ៍','ថ្ងៃសុក្រ','ថ្ងៃសៅរ៍'];
    const gregMonthsEn = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const gregMonthsKh = ['មករា','កុម្ភៈ','មីនា','មេសា','ឧសភា','មិថុនា','កក្កដា','សីហា','កញ្ញា','តុលា','វិច្ឆិកា','ធ្នូ'];
    
    const toKhmerNum = n => String(n).split('').map(d => khmerNums[+d] ?? d).join('');

    // Fixed & Dynamic Cambodian Holidays
    const solarHolidays = {
        '01-01': { name: 'ទិវាចូលឆ្នាំសកល (International New Year\'s Day)', type: 'red' },
        '01-07': { name: 'ទិវាជ័យជម្នះលើរបបប្រល័យពូជសាសន៍ (Victory Over Genocide Day)', type: 'red' },
        '03-08': { name: 'ទិវានារីអន្តរជាតិ (International Women\'s Day)', type: 'red' },
        '04-14': { name: 'ពិធីបុណ្យចូលឆ្នាំថ្មី ប្រពៃណីជាតិខ្មែរ (Khmer New Year Day 1)', type: 'red' },
        '04-15': { name: 'ពិធីបុណ្យចូលឆ្នាំថ្មី ប្រពៃណីជាតិខ្មែរ (Khmer New Year Day 2)', type: 'red' },
        '04-16': { name: 'ពិធីបុណ្យចូលឆ្នាំថ្មី ប្រពៃណីជាតិខ្មែរ (Khmer New Year Day 3)', type: 'red' },
        '05-01': { name: 'ទិវាពលកម្មអន្តរជាតិ (International Labor Day)', type: 'red' },
        '05-14': { name: 'ព្រះរាជពិធីបុណ្យចម្រើនព្រះជន្ម ព្រះករុណាព្រះបាទសម្តេចព្រះបរមនាថ នរោត្តម សីហមុនី (King\'s Birthday)', type: 'red' },
        '06-18': { name: 'ព្រះរាជពិធីបុណ្យចម្រើនព្រះជន្ម សម្តេចព្រះមហាក្សត្រី នរោត្តម មុនិនាថ សីហនុ (Queen Mother\'s Birthday)', type: 'red' },
        '07-01': { name: 'ទិវាមច្ឆាជាតិ (National Fish Day)', type: 'blue' },
        '07-07': { name: 'ខួបប្រាសាទព្រះវិហារ និងសំបូរព្រៃគុក ចូលជាបេតិកភណ្ឌពិភពលោក (UNESCO Heritage Anniversary)', type: 'blue' },
        '07-09': { name: 'ទិវារុក្ខទិវា (National Arbor Day)', type: 'blue' },
        '09-24': { name: 'ទិវាប្រកាសរដ្ឋធម្មនុញ្ញ (Constitutional Day)', type: 'red' },
        '10-01': { name: 'ទិវាមនុស្សចាស់កម្ពុជា និងទិវាមនុស្សចាស់អន្តរជាតិ (Cambodian & International Older Persons Day)', type: 'blue' },
        '10-05': { name: 'ទិវាគ្រូបង្រៀនពិភពលោក (World Teachers\' Day)', type: 'blue' },
        '10-15': { name: 'ទិវាគោរពព្រះវិញ្ញាណក្ខន្ធ ព្រះបរមរតនកោដ្ឋ (Commemoration Day of King Father)', type: 'red' },
        '10-23': { name: 'ទិវាកិច្ចព្រមព្រៀងសន្តិភាពទីក្រុងប៉ារីស (Paris Peace Agreement Day)', type: 'blue' },
        '10-29': { name: 'ព្រះរាជពិធីគ្រងព្រះបរមរាជសម្បត្តិ ព្រះករុណាព្រះបាទសម្តេចព្រះបរមនាថ នរោត្តម សីហមុនី (Coronation Day)', type: 'red' },
        '11-09': { name: 'ទិវាបុណ្យឯករាជ្យជាតិ (National Independence Day)', type: 'red' },
        '12-29': { name: 'ទិវាសន្តិភាពនៅកម្ពុជា (Peace Day in Cambodia)', type: 'red' }
    };

    // Exact Chankkasik lunar month schedule for Cambodian Calendar (2025-2027 anchor checkpoints)
    const khmerLunarSchedule = [
        { start: '2025-11-21', monthIdx: 0, days: 29, zodiac: 'ម្សាញ់ (Snake)', be: 2569 },
        { start: '2025-12-20', monthIdx: 1, days: 29, zodiac: 'ម្សាញ់ (Snake)', be: 2569 },
        { start: '2026-01-18', monthIdx: 2, days: 30, zodiac: 'ម្សាញ់ (Snake)', be: 2569 },
        { start: '2026-02-17', monthIdx: 3, days: 29, zodiac: 'ម្សាញ់ (Snake)', be: 2569 },
        { start: '2026-03-18', monthIdx: 4, days: 30, zodiac: 'ម្សាញ់ (Snake)', be: 2569 },
        { start: '2026-04-17', monthIdx: 5, days: 29, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2026-05-16', monthIdx: 6, days: 30, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2026-06-15', monthIdx: 7, days: 30, zodiac: 'មមី (Horse)', be: 2570, nameKh: 'បឋមាសាឍ', nameEn: 'Pathamasadha' },
        { start: '2026-07-15', monthIdx: 7, days: 29, zodiac: 'មមី (Horse)', be: 2570, nameKh: 'ទុតិយាសាឍ', nameEn: 'Dutiyasadha' },
        { start: '2026-08-13', monthIdx: 8, days: 30, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2026-09-12', monthIdx: 9, days: 30, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2026-10-12', monthIdx: 10, days: 29, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2026-11-10', monthIdx: 11, days: 30, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2026-12-10', monthIdx: 0, days: 29, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2027-01-08', monthIdx: 1, days: 30, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2027-02-07', monthIdx: 2, days: 29, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2027-03-08', monthIdx: 3, days: 30, zodiac: 'មមី (Horse)', be: 2570 },
        { start: '2027-04-07', monthIdx: 4, days: 29, zodiac: 'មមែ (Goat)', be: 2571 }
    ];

    function calculateLunarDate(targetDate) {
        const d = new Date(targetDate.getFullYear(), targetDate.getMonth(), targetDate.getDate(), 12, 0, 0);
        const yyyymmdd = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        
        let matched = null;
        for (let i = 0; i < khmerLunarSchedule.length - 1; i++) {
            if (yyyymmdd >= khmerLunarSchedule[i].start && yyyymmdd < khmerLunarSchedule[i+1].start) {
                matched = khmerLunarSchedule[i];
                break;
            }
        }
        if (!matched && yyyymmdd >= khmerLunarSchedule[khmerLunarSchedule.length - 1].start) {
            matched = khmerLunarSchedule[khmerLunarSchedule.length - 1];
        }

        let lunarDayRaw, halfDay, isWaxing, moonPhase, lunarMonthIdx, lunarMonthKh, lunarMonthEn, khmerYear, beYear, zodiac, isSila;

        if (matched) {
            const [sYear, sMonth, sDay] = matched.start.split('-').map(Number);
            const startDate = new Date(sYear, sMonth - 1, sDay, 12, 0, 0);
            const diffDays = Math.round((d.getTime() - startDate.getTime()) / (1000 * 60 * 60 * 24)) + 1;
            lunarDayRaw = diffDays;
            isWaxing = lunarDayRaw <= 15;
            halfDay = isWaxing ? lunarDayRaw : lunarDayRaw - 15;
            moonPhase = isWaxing ? 'ខ្នើត' : 'រោច';
            lunarMonthIdx = matched.monthIdx;
            lunarMonthKh = matched.nameKh || khmerMonths[lunarMonthIdx];
            lunarMonthEn = matched.nameEn || khmerMonthsEn[lunarMonthIdx];
            khmerYear = d.getFullYear() + 638;
            beYear = matched.be;
            zodiac = matched.zodiac;
            
            const waningEndDay = matched.days - 15;
            isSila = (halfDay === 8 || (isWaxing && halfDay === 15) || (!isWaxing && halfDay === waningEndDay));
        } else {
            const synodicMonth = 29.53058867;
            const jd = Math.floor(365.25 * (d.getFullYear() + 4716)) + Math.floor(30.6001 * (d.getMonth() + 2)) + d.getDate() - 1524.5;
            const daysSince = (jd - 2451549.5) % synodicMonth;
            lunarDayRaw = Math.floor(((daysSince % synodicMonth) + synodicMonth) % synodicMonth) + 1;
            lunarMonthIdx = ((Math.floor((jd - 2451549.5) / synodicMonth + 0.5)) % 12 + 12) % 12;
            isWaxing = lunarDayRaw <= 15;
            halfDay = isWaxing ? lunarDayRaw : lunarDayRaw - 15;
            moonPhase = isWaxing ? 'ខ្នើត' : 'រោច';
            isSila = (halfDay === 8 || (isWaxing && halfDay === 15) || (!isWaxing && (halfDay === 14 || halfDay === 15)));
            lunarMonthKh = khmerMonths[lunarMonthIdx];
            lunarMonthEn = khmerMonthsEn[lunarMonthIdx];
            khmerYear = d.getFullYear() + 638;
            beYear = d.getFullYear() + 544;
            const zodiacIdx = (khmerYear - 4) % 12;
            zodiac = khmerZodiacs[(zodiacIdx + 12) % 12];
        }

        // Check for Lunar Holidays
        let holiday = null;
        let holidayType = null; // 'red' (Public Holiday) or 'blue' (Traditional Observance)

        if (lunarMonthIdx === 2 && isWaxing && halfDay === 15) { holiday = 'ពិធីបុណ្យមាឃបូជា (Meak Bochea Day)'; holidayType = 'blue'; }
        if (lunarMonthIdx === 5 && isWaxing && halfDay === 15) { holiday = 'ពិធីបុណ្យវិសាខបូជា (Visak Bochea Day)'; holidayType = 'red'; }
        if (lunarMonthIdx === 5 && !isWaxing && halfDay === 4) { holiday = 'ព្រះរាជពិធីច្រត់ព្រះនង្គ័ល (Royal Plowing Ceremony)'; holidayType = 'red'; }
        
        // Asalha Bochea & Beginning of Buddhist Lent
        if (lunarMonthIdx === 7 && isWaxing && halfDay === 15) { holiday = 'ពិធីបុណ្យអាសាឍបូជា (Asalha Bochea Day)'; holidayType = 'blue'; }
        if (lunarMonthIdx === 7 && !isWaxing && halfDay === 1) { holiday = 'ចូលវស្សា (Beginning of Buddhist Lent)'; holidayType = 'blue'; }
        
        // Pchum Ben checks (1-13 Kan Ben [Blue], 14-15 Pchum Ben Day 1 & 2 [Red], Day 3 on 1 Waxing of Asvina [Red])
        const bhadrapadaWaningDays = (matched && matched.monthIdx === 9) ? (matched.days - 15) : 15;
        if (lunarMonthIdx === 9 && !isWaxing && halfDay >= 1 && halfDay <= bhadrapadaWaningDays - 2) {
            holiday = `ពិធីបុណ្យកាន់បិណ្ឌ ទី ${halfDay} (Kan Ben Day ${halfDay})`;
            holidayType = 'blue';
        } else if (lunarMonthIdx === 9 && !isWaxing && halfDay === bhadrapadaWaningDays - 1) {
            holiday = 'ពិធីបុណ្យភ្ជុំបិណ្ឌ ថ្ងៃទី ១ (Pchum Ben Day 1)';
            holidayType = 'red';
        } else if (lunarMonthIdx === 9 && !isWaxing && halfDay === bhadrapadaWaningDays) {
            holiday = 'ពិធីបុណ្យភ្ជុំបិណ្ឌ ថ្ងៃទី ២ (Pchum Ben Day 2)';
            holidayType = 'red';
        } else if (lunarMonthIdx === 10 && isWaxing && halfDay === 1) {
            holiday = 'ពិធីបុណ្យភ្ជុំបិណ្ឌ ថ្ងៃទី ៣ (Pchum Ben Day 3 / Main Day)';
            holidayType = 'red';
        }

        // Ending of Buddhist Lent & Kathen Kal in Asvina
        if (lunarMonthIdx === 10 && isWaxing && halfDay === 15) { holiday = 'ចេញវស្សា (Ending of Buddhist Lent)'; holidayType = 'blue'; }
        if (lunarMonthIdx === 10 && !isWaxing && halfDay === 1) { holiday = 'កឋិនកាល (Kathen Kal)'; holidayType = 'blue'; }

        // Water Festival checks (14 Waxing, 15 Waxing, 1 Waning of Kattika)
        if (lunarMonthIdx === 11 && isWaxing && halfDay === 14) { holiday = 'ព្រះរាជពិធីបុណ្យអុំទូក (Water Festival Day 1)'; holidayType = 'red'; }
        if (lunarMonthIdx === 11 && isWaxing && halfDay === 15) { holiday = 'ព្រះរាជពិធីបុណ្យអុំទូក (Water Festival Day 2)'; holidayType = 'red'; }
        if (lunarMonthIdx === 11 && !isWaxing && halfDay === 1) { holiday = 'ព្រះរាជពិធីបុណ្យអុំទូក (Water Festival Day 3)'; holidayType = 'red'; }
        
        // Solar Holiday takes precedence or appends
        const mmdd = String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        if (solarHolidays[mmdd]) {
            const solObj = solarHolidays[mmdd];
            if (holiday) {
                holiday = `${solObj.name} / ${holiday}`;
                if (solObj.type === 'red') holidayType = 'red';
            } else {
                holiday = solObj.name;
                holidayType = solObj.type;
            }
        }

        return {
            lunarDayRaw, halfDay, isWaxing, moonPhase, lunarMonthIdx,
            lunarMonthKh, lunarMonthEn,
            khmerYear, beYear, zodiac, isSila, holiday, holidayType, gregDate: d
        };
    }

    // Initialize Header Widget
    const todayLunar = calculateLunarDate(now);
    document.getElementById('khmer-day').textContent = toKhmerNum(todayLunar.halfDay);
    document.getElementById('khmer-month-year').textContent = `${todayLunar.lunarMonthKh} ${toKhmerNum(todayLunar.khmerYear)}`;
    document.getElementById('khmer-lunar-day').textContent = `${todayLunar.moonPhase} ទី ${toKhmerNum(todayLunar.halfDay)}`;

    // Modal State & Controls
    let modalYear = now.getFullYear();
    let modalMonth = now.getMonth();
    let selectedDate = new Date(now);

    window.openKhmerCalendarModal = function() {
        modalYear = now.getFullYear();
        modalMonth = now.getMonth();
        selectedDate = new Date(now);
        renderKhmerCalendarModal();
        const modal = document.getElementById('khmer-calendar-modal');
        const content = document.getElementById('khmer-calendar-modal-content');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100', 'pointer-events-auto');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    };

    window.closeKhmerCalendarModal = function() {
        const modal = document.getElementById('khmer-calendar-modal');
        const content = document.getElementById('khmer-calendar-modal-content');
        modal.classList.remove('opacity-100', 'pointer-events-auto');
        modal.classList.add('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
    };

    window.closeKhmerCalendarOnBackdrop = function(e) {
        if (e.target.id === 'khmer-calendar-modal') {
            closeKhmerCalendarModal();
        }
    };

    window.changeKhmerCalendarMonth = function(delta) {
        modalMonth += delta;
        if (modalMonth < 0) { modalMonth = 11; modalYear--; }
        else if (modalMonth > 11) { modalMonth = 0; modalYear++; }
        renderKhmerCalendarModal();
    };

    window.resetKhmerCalendarMonth = function() {
        modalYear = now.getFullYear();
        modalMonth = now.getMonth();
        selectedDate = new Date(now);
        renderKhmerCalendarModal();
    };

    function renderKhmerCalendarModal() {
        // Top sub-header updates
        document.getElementById('khmer-modal-month-name').textContent = `${gregMonthsKh[modalMonth]} (${gregMonthsEn[modalMonth]}) ${modalYear}`;
        
        const firstDay = new Date(modalYear, modalMonth, 1);
        const firstLunar = calculateLunarDate(firstDay);
        document.getElementById('khmer-modal-year-details').innerHTML = `
            <i class="bi bi-calendar-event text-hotel-gold"></i>
            <span>ឆ្នាំ${firstLunar.zodiac} ព.ស. ${toKhmerNum(firstLunar.beYear)} &mdash; គ.ស. ${toKhmerNum(modalYear)}</span>
        `;

        // Render Grid
        const grid = document.getElementById('khmer-calendar-grid');
        grid.innerHTML = '';
        
        const startDayOfWeek = firstDay.getDay(); // 0 (Sun) to 6 (Sat)
        const daysInMonth = new Date(modalYear, modalMonth + 1, 0).getDate();

        // Empty padding slots
        for (let i = 0; i < startDayOfWeek; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'min-h-[64px] sm:min-h-[72px] p-1.5 rounded-2xl bg-gray-50/50 dark:bg-gray-900/30 border border-transparent opacity-40';
            grid.appendChild(emptyCell);
        }

        // Days
        let holidaysInMonth = [];
        for (let dayNum = 1; dayNum <= daysInMonth; dayNum++) {
            const cellDate = new Date(modalYear, modalMonth, dayNum);
            const lInfo = calculateLunarDate(cellDate);
            const isToday = cellDate.toDateString() === now.toDateString();
            const isSelected = cellDate.toDateString() === selectedDate.toDateString();

            if (lInfo.holiday) {
                holidaysInMonth.push({ date: cellDate, lunar: lInfo, name: lInfo.holiday });
            }

            const cell = document.createElement('div');
            let baseClasses = 'min-h-[64px] sm:min-h-[72px] p-1.5 sm:p-2 rounded-2xl border transition relative flex flex-col justify-between cursor-pointer select-none group ';
            if (isToday) {
                baseClasses += 'bg-hotel-gold/15 dark:bg-hotel-gold/20 border-hotel-gold shadow-md ';
            } else if (isSelected) {
                baseClasses += 'bg-blue-50 dark:bg-blue-950/40 border-blue-400 dark:border-blue-500 ';
            } else {
                baseClasses += 'bg-white dark:bg-[#1a2534] border-gray-100 dark:border-gray-800 hover:border-hotel-gold/60 hover:shadow-sm ';
            }
            cell.className = baseClasses;

            cell.onclick = () => {
                selectedDate = cellDate;
                renderKhmerCalendarModal();
            };

            const topRow = document.createElement('div');
            topRow.className = 'flex items-center justify-between';
            
            const gregSpan = document.createElement('span');
            gregSpan.className = `font-bold text-base md:text-lg leading-none ${isToday ? 'text-hotel-dark dark:text-white underline decoration-hotel-gold decoration-2' : 'text-hotel-dark dark:text-gray-200'}`;
            gregSpan.textContent = dayNum;
            topRow.appendChild(gregSpan);

            if (lInfo.isSila) {
                const silaIcon = document.createElement('span');
                silaIcon.className = 'text-xs md:text-sm' + (isToday ? ' animate-bounce' : '');
                silaIcon.title = 'ថ្ងៃសីល (Buddhist Holy Day / Sila)';
                silaIcon.textContent = '🙏';
                topRow.appendChild(silaIcon);
            }
            cell.appendChild(topRow);

            const bottomRow = document.createElement('div');
            bottomRow.className = 'mt-1 flex flex-col items-start gap-0.5';

            const khmerSpan = document.createElement('span');
            khmerSpan.className = 'text-[0.7rem] md:text-xs font-semibold text-hotel-gold leading-tight';
            khmerSpan.textContent = `${toKhmerNum(lInfo.halfDay)} ${lInfo.moonPhase}`;
            bottomRow.appendChild(khmerSpan);

            if (lInfo.holiday) {
                const holBadge = document.createElement('span');
                const badgeTheme = (lInfo.holidayType === 'blue')
                    ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-800/60'
                    : 'bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border-red-200/80 dark:border-red-800/60 font-semibold';
                holBadge.className = `w-full text-[0.58rem] sm:text-[0.62rem] px-1 py-0.5 rounded border leading-tight truncate font-sans ${badgeTheme}`;
                holBadge.textContent = lInfo.holiday.split('(')[0].trim();
                holBadge.title = lInfo.holiday;
                bottomRow.appendChild(holBadge);
            }

            cell.appendChild(bottomRow);
            grid.appendChild(cell);
        }

        // Render Sidebar Details for selectedDate
        const selLunar = calculateLunarDate(selectedDate);
        document.getElementById('khmer-modal-selected-greg').textContent = `${gregMonthsEn[selectedDate.getMonth()]} ${selectedDate.getDate()}, ${selectedDate.getFullYear()}`;
        
        const wdayKh = khmerWeekdaysFull[selectedDate.getDay()];
        const fullKhmerString = `${wdayKh} ទី ${toKhmerNum(selectedDate.getDate())} ខែ${gregMonthsKh[selectedDate.getMonth()]} ឆ្នាំ${toKhmerNum(selectedDate.getFullYear())} <br><span class="text-white/90 font-normal mt-1.5 block">ត្រូវនឹង${wdayKh} ${toKhmerNum(selLunar.halfDay)} ${selLunar.moonPhase} ខែ${selLunar.lunarMonthKh} ឆ្នាំ${selLunar.zodiac} ពុទ្ធសករាជ ${toKhmerNum(selLunar.beYear)}</span>`;
        document.getElementById('khmer-modal-selected-full-text').innerHTML = fullKhmerString;

        const silaBanner = document.getElementById('khmer-modal-sila-banner');
        if (selLunar.isSila) { silaBanner.classList.remove('hidden'); } else { silaBanner.classList.add('hidden'); }

        const holBanner = document.getElementById('khmer-modal-holiday-banner');
        if (selLunar.holiday) {
            document.getElementById('khmer-modal-holiday-banner-text').textContent = selLunar.holiday;
            holBanner.classList.remove('hidden');
        } else {
            holBanner.classList.add('hidden');
        }

        // Populate Monthly Holidays List
        const holList = document.getElementById('khmer-modal-holidays-list');
        document.getElementById('khmer-modal-holiday-count').textContent = holidaysInMonth.length;
        holList.innerHTML = '';

        if (holidaysInMonth.length === 0) {
            holList.innerHTML = '<li class="text-gray-400 italic py-6 text-center">ពុំមានបុណ្យជាតិក្នុងខែនេះទេ (No major public holidays this month)</li>';
        } else {
            holidaysInMonth.forEach(h => {
                const li = document.createElement('li');
                li.className = 'p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 flex items-start gap-2.5 hover:border-hotel-gold/40 transition cursor-pointer';
                li.onclick = () => {
                    selectedDate = h.date;
                    renderKhmerCalendarModal();
                };
                const iconTheme = (h.lunar.holidayType === 'blue')
                    ? 'bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400'
                    : 'bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400 font-bold';
                li.innerHTML = `
                    <div class="w-8 h-8 rounded-lg ${iconTheme} flex flex-col items-center justify-center shrink-0 leading-none">
                        <span class="text-xs font-bold">${h.date.getDate()}</span>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-hotel-dark dark:text-white leading-snug">${h.name}</div>
                        <div class="text-[0.65rem] text-hotel-gold mt-0.5">${khmerWeekdaysFull[h.date.getDay()]} &bull; ${toKhmerNum(h.lunar.halfDay)} ${h.lunar.moonPhase} ខែ${h.lunar.lunarMonthKh}</div>
                    </div>
                `;
                holList.appendChild(li);
            });
        }
    }


    // ── Phnom Penh Weather (Open-Meteo, no key needed) ─────────
    fetch('https://api.open-meteo.com/v1/forecast?latitude=11.56&longitude=104.93&current=temperature_2m,weathercode&timezone=Asia%2FPhnom_Penh')
        .then(r => r.json())
        .then(data => {
            const temp  = Math.round(data.current.temperature_2m);
            const code  = data.current.weathercode;
            const icons = {0:'☀️',1:'🌤️',2:'⛅',3:'☁️',45:'🌫️',48:'🌫️',51:'🌦️',53:'🌦️',55:'🌧️',61:'🌧️',63:'🌧️',65:'🌧️',80:'🌦️',81:'🌧️',82:'⛈️',95:'⛈️',96:'⛈️',99:'⛈️'};
            const descs = {0:'Clear sky',1:'Mainly clear',2:'Partly cloudy',3:'Overcast',45:'Foggy',48:'Foggy',51:'Light drizzle',53:'Drizzle',55:'Heavy drizzle',61:'Light rain',63:'Rain',65:'Heavy rain',80:'Showers',81:'Showers',82:'Heavy showers',95:'Thunderstorm',96:'Thunderstorm',99:'Thunderstorm'};
            document.getElementById('weather-icon').textContent = icons[code] ?? '🌤️';
            document.getElementById('weather-temp').textContent = temp+'°C';
            document.getElementById('weather-desc').textContent = descs[code] ?? 'N/A';
        })
        .catch(() => { document.getElementById('weather-desc').textContent = 'Unavailable'; });
});
</script>
@endpush
