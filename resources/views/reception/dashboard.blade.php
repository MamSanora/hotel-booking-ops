@extends('layouts.public')

@section('title', 'Receptionist Dashboard')

@section('content')

{{-- ==========================================
     DASHBOARD HEADER
     ========================================== --}}
<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="font-playfair text-3xl md:text-4xl font-bold mb-2">Reception Desk</h1>
                <p class="text-white/70 text-[0.95rem]">Manage today's arrivals, departures, and in-house guests.</p>
            </div>
            <a href="{{ route('reception.walkin.create') }}" class="bg-hotel-gold hover:bg-yellow-600 text-white px-5 py-2.5 rounded-xl font-semibold transition-colors flex items-center shadow-lg shadow-hotel-gold/20">
                <i class="bi bi-person-plus-fill mr-2"></i> New Walk-In Booking
            </a>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-10">
    
    {{-- Alerts --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-8">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle text-green-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-600 hover:text-green-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" class="flex justify-between items-center bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-8">
            <div class="flex items-center gap-3">
                <i class="bi bi-exclamation-triangle text-red-600 text-lg"></i>
                <span class="text-[0.95rem] font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    <div class="space-y-8">
        
        {{-- ==========================================
             ROOM SERVICE REQUESTS
             ========================================== --}}
        @if(isset($pendingRoomServices) && $pendingRoomServices->count() > 0)
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8 border-2 border-amber-200">
            <h3 class="font-playfair text-[1.4rem] font-bold text-hotel-dark pb-3 border-b-2 border-gray-100 mb-6 flex items-center">
                <i class="bi bi-bell-fill text-amber-500 mr-3 animate-pulse"></i>
                Pending Room Service Requests ({{ $pendingRoomServices->count() }})
            </h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-amber-50 text-amber-700 text-[0.8rem] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4 font-semibold rounded-tl-xl rounded-bl-xl">Room</th>
                            <th class="px-5 py-4 font-semibold">Guest</th>
                            <th class="px-5 py-4 font-semibold">Request Details</th>
                            <th class="px-5 py-4 font-semibold rounded-tr-xl rounded-br-xl text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pendingRoomServices as $rs)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <strong class="text-gray-800 text-[1.05rem]">Room {{ $rs->booking->room?->room_number ?? '-' }}</strong>
                                <div class="text-[0.75rem] text-gray-500 mt-1">{{ $rs->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-800 text-[0.95rem]">{{ $rs->booking->guest?->full_name ?? 'Guest' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($rs->requestedItems->isNotEmpty())
                                    <div class="text-sm font-medium text-gray-800 mb-1">Items:</div>
                                    <ul class="list-disc list-inside text-[0.85rem] text-gray-600 mb-2">
                                        @foreach($rs->requestedItems as $item)
                                            <li>{{ $item->amount_per_item }}x {{ $item->catalog->item_name ?? 'Unknown Item' }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if($rs->guest_notes)
                                    <div class="text-[0.85rem] text-gray-700 bg-gray-50 p-2 rounded border border-gray-200 italic">"{{ $rs->guest_notes }}"</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('reception.room-service.complete', $rs->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="response" placeholder="Reply (optional)..." class="text-sm border border-gray-300 rounded px-2 py-1.5 mb-2 w-full max-w-[200px] inline-block">
                                    <br>
                                    <button type="submit" onclick="return confirm('Mark this request as completed?')" class="inline-flex items-center bg-amber-100 hover:bg-amber-200 text-amber-800 font-semibold px-3 py-1.5 rounded-lg text-sm transition-colors border border-amber-300">
                                        <i class="bi bi-check2-circle mr-1.5"></i>Mark Completed
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

        {{-- ==========================================
             TODAY'S GUEST MOVEMENT
             ========================================== --}}
        <div>
            <h2 class="font-playfair text-2xl font-bold text-hotel-dark border-b-2 border-gray-200 pb-3 mb-6 flex items-center">
                <i class="bi bi-people text-teal-500 mr-3"></i>Today's Guest Movement
                <span class="ml-3 text-sm font-normal text-gray-400">{{ now()->format('l, F j, Y') }}</span>
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Arrivals List --}}
                <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] border border-[#f0ebe2] overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-teal-50/50">
                        <div class="w-9 h-9 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center"><i class="bi bi-box-arrow-in-right text-lg"></i></div>
                        <div>
                            <div class="font-semibold text-hotel-dark text-sm">Check-Ins Today</div>
                            <div class="text-teal-600 text-xs font-bold">{{ $arrivalsToday->count() }} guest{{ $arrivalsToday->count() !== 1 ? 's' : '' }} arriving</div>
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
                            <div class="text-red-500 text-xs font-bold">{{ $todayDepartures->count() }} guest{{ $todayDepartures->count() !== 1 ? 's' : '' }} departing</div>
                        </div>
                    </div>
                    @if($todayDepartures->count() > 0)
                        <ul class="divide-y divide-gray-50">
                            @foreach($todayDepartures as $booking)
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
        </div>

        {{-- ==========================================
             TABBED OPERATIONS: ARRIVALS, DEPARTURES, IN-HOUSE, HISTORY
             ========================================== --}}
        <div x-data="{ activeTab: 'arrivals' }" class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8 border border-[#f0ebe2]">
            {{-- Tab Navigation Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b-2 border-gray-100 mb-6">
                <div class="flex flex-wrap items-center gap-2.5">
                    {{-- Tab 1: Upcoming Arrivals --}}
                    <button type="button" @click="activeTab = 'arrivals'"
                        :class="activeTab === 'arrivals' 
                            ? 'bg-hotel-gold text-white shadow-md shadow-hotel-gold/20 font-bold border-hotel-gold' 
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 font-semibold border-transparent'"
                        class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2 border">
                        <i class="bi bi-box-arrow-in-right" :class="activeTab === 'arrivals' ? 'text-white' : 'text-green-500'"></i>
                        <span>Upcoming Arrivals</span>
                        <span :class="activeTab === 'arrivals' ? 'bg-white/20 text-white' : 'bg-white text-gray-700'" class="text-xs font-bold px-2 py-0.5 rounded-full ml-1">{{ $upcomingArrivals->count() }}</span>
                    </button>

                    {{-- Tab 2: Today's Departures --}}
                    <button type="button" @click="activeTab = 'departures'"
                        :class="activeTab === 'departures' 
                            ? 'bg-hotel-gold text-white shadow-md shadow-hotel-gold/20 font-bold border-hotel-gold' 
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 font-semibold border-transparent'"
                        class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2 border">
                        <i class="bi bi-box-arrow-right" :class="activeTab === 'departures' ? 'text-white' : 'text-yellow-500'"></i>
                        <span>Today's Departures</span>
                        <span :class="activeTab === 'departures' ? 'bg-white/20 text-white' : 'bg-white text-gray-700'" class="text-xs font-bold px-2 py-0.5 rounded-full ml-1">{{ $todayDepartures->count() }}</span>
                    </button>

                    {{-- Tab 3: In-House Guests --}}
                    <button type="button" @click="activeTab = 'inhouse'"
                        :class="activeTab === 'inhouse' 
                            ? 'bg-hotel-gold text-white shadow-md shadow-hotel-gold/20 font-bold border-hotel-gold' 
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 font-semibold border-transparent'"
                        class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2 border">
                        <i class="bi bi-house-door" :class="activeTab === 'inhouse' ? 'text-white' : 'text-blue-500'"></i>
                        <span>In-House Guests</span>
                        <span :class="activeTab === 'inhouse' ? 'bg-white/20 text-white' : 'bg-white text-gray-700'" class="text-xs font-bold px-2 py-0.5 rounded-full ml-1">{{ $inHouseGuests->count() }}</span>
                    </button>

                    {{-- Tab 4: Recent History --}}
                    <button type="button" @click="activeTab = 'history'"
                        :class="activeTab === 'history' 
                            ? 'bg-hotel-gold text-white shadow-md shadow-hotel-gold/20 font-bold border-hotel-gold' 
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 font-semibold border-transparent'"
                        class="px-5 py-2.5 rounded-xl text-sm transition-all flex items-center gap-2 border">
                        <i class="bi bi-clock-history" :class="activeTab === 'history' ? 'text-white' : 'text-purple-500'"></i>
                        <span>Recent History</span>
                        <span :class="activeTab === 'history' ? 'bg-white/20 text-white' : 'bg-white text-gray-700'" class="text-xs font-bold px-2 py-0.5 rounded-full ml-1">{{ $recentHistory->count() }}</span>
                    </button>
                </div>

                {{-- Context description --}}
                <div class="text-xs font-medium text-gray-400 hidden lg:block">
                    <span x-show="activeTab === 'arrivals'"><i class="bi bi-info-circle mr-1"></i>Confirmed bookings arriving today or in the future</span>
                    <span x-show="activeTab === 'departures'" x-cloak><i class="bi bi-info-circle mr-1"></i>Checked-in guests scheduled to depart today</span>
                    <span x-show="activeTab === 'inhouse'" x-cloak><i class="bi bi-info-circle mr-1"></i>Currently occupied rooms & extensions</span>
                    <span x-show="activeTab === 'history'" x-cloak><i class="bi bi-info-circle mr-1"></i>Activity over the last 14 days (scrollable)</span>
                </div>
            </div>

            {{-- ==========================================
                 TAB 1 CONTENT: UPCOMING ARRIVALS
                 ========================================== --}}
            <div x-show="activeTab === 'arrivals'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <h3 class="font-playfair text-[1.25rem] font-bold text-hotel-dark mb-4 flex items-center">
                    <i class="bi bi-box-arrow-in-right text-green-500 mr-2"></i> Upcoming Arrivals
                </h3>
                @if($upcomingArrivals->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                                <tr>
                                    <th class="px-5 py-4 font-semibold rounded-tl-xl rounded-bl-xl">Ref</th>
                                    <th class="px-5 py-4 font-semibold">Guest Name</th>
                                    <th class="px-5 py-4 font-semibold">Arrival Date</th>
                                    <th class="px-5 py-4 font-semibold">Room</th>
                                    <th class="px-5 py-4 font-semibold">Payment</th>
                                    <th class="px-5 py-4 font-semibold rounded-tr-xl rounded-br-xl text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($upcomingArrivals as $booking)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <strong class="font-playfair text-hotel-gold text-lg">{{ $booking->referenceNumber() }}</strong>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800 text-[0.95rem]">
                                            {{ $booking->guest?->full_name ?? 'Walk-in Guest' }}
                                        </div>
                                        <div class="text-gray-500 text-[0.8rem] mt-0.5">
                                            {{ $booking->guest?->phones?->first()?->phone_number ?? '—' }}
                                        </div>
                                        @if($booking->special_requests)
                                            <div class="mt-1.5 p-1.5 bg-amber-50 border border-amber-200 rounded text-amber-800 text-[0.78rem] flex items-start gap-1 max-w-xs">
                                                <i class="bi bi-chat-left-text-fill text-amber-600 mt-0.5 shrink-0"></i>
                                                <span><strong>Request:</strong> {{ $booking->special_requests }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-800 text-[0.95rem]">
                                            {{ $booking->check_in_date->format('M d, Y') }}
                                        </div>
                                        @if($booking->check_in_date->isToday())
                                            <span class="bg-green-100 text-green-800 text-[0.7rem] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Today</span>
                                        @elseif($booking->check_in_date->isTomorrow())
                                            <span class="bg-blue-100 text-blue-800 text-[0.7rem] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Tomorrow</span>
                                        @else
                                            <span class="text-gray-500 text-[0.75rem] font-medium">{{ $booking->check_in_date->diffForHumans() }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-gray-800 font-medium text-[0.95rem]">{{ $booking->room?->displayType() ?? 'N/A' }}</div>
                                        <div class="text-gray-500 text-[0.8rem] mt-0.5">Room {{ $booking->room?->room_number ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @php $paid = $booking->transactions?->where('payment_status', 'full')->count() > 0; @endphp
                                        @if($paid)
                                            <span class="bg-green-100 text-green-800 text-[0.75rem] font-bold px-3 py-1 rounded-full">Paid</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 text-[0.75rem] font-bold px-3 py-1 rounded-full">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right space-x-2">
                                        @if(!$paid)
                                            <form action="{{ route('reception.payment.manual', $booking->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="payment_method" value="cash">
                                                <input type="hidden" name="amount" value="{{ $booking->total_price }}">
                                                <button type="submit" onclick="return confirm('Mark as paid via Cash?')" class="inline-flex items-center bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold px-3 py-2 rounded-lg text-sm transition-colors border border-blue-200" title="Receive Cash">
                                                    <i class="bi bi-cash mr-1"></i>Pay
                                                </button>
                                            </form>
                                        @endif
                                        @if($booking->check_in_date->startOfDay()->lte(now()->startOfDay()))
                                            <form action="{{ route('reception.checkin', $booking->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Check in this guest?')" class="inline-flex items-center bg-green-100 hover:bg-green-200 text-green-700 font-semibold px-3 py-2 rounded-lg text-sm transition-colors border border-green-200">
                                                    <i class="bi bi-check2-square mr-1"></i>Check In
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" disabled class="inline-flex items-center bg-gray-100 text-gray-400 font-semibold px-3 py-2 rounded-lg text-sm border border-gray-200 cursor-not-allowed" title="Cannot check in before arrival date">
                                                <i class="bi bi-check2-square mr-1"></i>Check In
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">No upcoming arrivals scheduled.</p>
                @endif
            </div>

            {{-- ==========================================
                 TAB 2 CONTENT: TODAY'S DEPARTURES
                 ========================================== --}}
            <div x-show="activeTab === 'departures'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <h3 class="font-playfair text-[1.25rem] font-bold text-hotel-dark mb-4 flex items-center">
                    <i class="bi bi-box-arrow-right text-yellow-500 mr-2"></i> Today's Departures
                </h3>
                @if($todayDepartures->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                                <tr>
                                    <th class="px-5 py-4 font-semibold rounded-tl-xl rounded-bl-xl">Ref</th>
                                    <th class="px-5 py-4 font-semibold">Guest Name</th>
                                    <th class="px-5 py-4 font-semibold">Room</th>
                                    <th class="px-5 py-4 font-semibold">Balance</th>
                                    <th class="px-5 py-4 font-semibold rounded-tr-xl rounded-br-xl text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($todayDepartures as $booking)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <strong class="font-playfair text-hotel-gold text-lg">{{ $booking->referenceNumber() }}</strong>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800 text-[0.95rem]">
                                            {{ $booking->guest?->full_name ?? 'Walk-in Guest' }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-gray-800 font-medium text-[0.95rem]">Room {{ $booking->room?->room_number ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @php $paid = $booking->transactions?->where('payment_status', 'full')->count() > 0; @endphp
                                        @if($paid)
                                            <span class="text-green-600 font-medium flex items-center gap-1.5"><i class="bi bi-check-circle"></i> Settled</span>
                                        @else
                                            <strong class="text-red-500 font-bold">Due: ${{ number_format($booking->total_price, 2) }}</strong>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <form action="{{ route('reception.checkout', $booking->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Check out this guest?')" class="inline-flex items-center bg-yellow-100 hover:bg-yellow-200 text-yellow-700 font-semibold px-4 py-2 rounded-lg text-sm transition-colors border border-yellow-200">
                                                <i class="bi bi-door-closed mr-2"></i>Check Out
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">No departures scheduled for today.</p>
                @endif
            </div>

            {{-- ==========================================
                 TAB 2 CONTENT: IN-HOUSE GUESTS
                 ========================================== --}}
            <div x-show="activeTab === 'inhouse'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <h3 class="font-playfair text-[1.25rem] font-bold text-hotel-dark mb-4 flex items-center">
                    <i class="bi bi-house-door text-blue-500 mr-2"></i> In-House Guests
                </h3>
                @if($inHouseGuests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                                <tr>
                                    <th class="px-5 py-4 font-semibold rounded-tl-xl rounded-bl-xl">Room</th>
                                    <th class="px-5 py-4 font-semibold">Guest Name</th>
                                    <th class="px-5 py-4 font-semibold">Check-In</th>
                                    <th class="px-5 py-4 font-semibold">Check-Out</th>
                                    <th class="px-5 py-4 font-semibold">Status</th>
                                    <th class="px-5 py-4 font-semibold rounded-tr-xl rounded-br-xl text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($inHouseGuests as $booking)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <strong class="text-gray-800 text-[0.95rem]">Room {{ $booking->room?->room_number ?? '-' }}</strong>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800 text-[0.95rem]">
                                            {{ $booking->guest?->full_name ?? 'Walk-in Guest' }}
                                        </div>
                                        <div class="text-gray-500 text-[0.8rem] mt-0.5">
                                            {{ $booking->guest?->phones?->first()?->phone_number ?? '—' }}
                                        </div>
                                        @if($booking->special_requests)
                                            <div class="mt-1.5 p-1.5 bg-amber-50 border border-amber-200 rounded text-amber-800 text-[0.78rem] flex items-start gap-1 max-w-xs">
                                                <i class="bi bi-chat-left-text-fill text-amber-600 mt-0.5 shrink-0"></i>
                                                <span><strong>Request:</strong> {{ $booking->special_requests }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-gray-700 text-[0.95rem] whitespace-nowrap">{{ $booking->check_in_date?->format('M d, Y') }}</td>
                                    <td class="px-5 py-4 text-gray-700 text-[0.95rem] whitespace-nowrap">{{ $booking->check_out_date?->format('M d, Y') }}</td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <span class="bg-green-100 text-green-800 text-[0.75rem] font-bold px-3 py-1 rounded-full tracking-wide">Checked In</span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right relative">
                                        @php
                                            $limit      = $extensionLimits[$booking->id] ?? ['max_nights' => 30, 'next_booking' => null];
                                            $maxNights  = $limit['max_nights'];
                                            $nextBook   = $limit['next_booking'];
                                            $blocked    = $maxNights === 0;
                                        @endphp
                                        <div class="flex items-center justify-end gap-2" x-data="{ showExtend: false }">

                                            {{-- Extend Stay button (hidden if blocked) --}}
                                            @if(! $blocked)
                                                <button type="button" @click="showExtend = !showExtend"
                                                    class="inline-flex items-center bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-emerald-200"
                                                    title="Extend Stay">
                                                    <i class="bi bi-calendar-plus mr-1"></i> Extend
                                                </button>
                                            @else
                                                {{-- Blocked: show Relocate button instead --}}
                                                <a href="{{ route('reception.relocate.show', $booking->id) }}"
                                                    class="inline-flex items-center bg-purple-100 hover:bg-purple-200 text-purple-800 font-semibold px-3 py-1.5 rounded-lg text-xs transition-colors border border-purple-200"
                                                    title="Extension impossible — relocate this guest">
                                                    <i class="bi bi-arrow-repeat mr-1"></i> Relocate
                                                </a>
                                            @endif

                                            {{-- Checkout button --}}
                                            <form action="{{ route('reception.checkout', $booking->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @if($booking->check_out_date && $booking->check_out_date->isFuture() && !$booking->check_out_date->isToday())
                                                    <button type="submit" onclick="return confirm('⚠️ EARLY DEPARTURE:\nThis guest was scheduled to leave on {{ $booking->check_out_date->format('M d, Y') }}.\nAre you sure you want to process an EARLY check-out for Room {{ $booking->room?->room_number ?? '-' }}?')" class="inline-flex items-center bg-orange-100 hover:bg-orange-200 text-orange-800 font-semibold px-3.5 py-1.5 rounded-lg text-xs transition-colors border border-orange-300 shadow-sm">
                                                        <i class="bi bi-box-arrow-right mr-1.5"></i>Early Check-Out
                                                    </button>
                                                @else
                                                    <button type="submit" onclick="return confirm('Check out guest from Room {{ $booking->room?->room_number ?? '-' }}?')" class="inline-flex items-center bg-yellow-100 hover:bg-yellow-200 text-yellow-700 font-semibold px-4 py-2 rounded-lg text-sm transition-colors border border-yellow-200">
                                                        <i class="bi bi-door-closed mr-2"></i>Check Out
                                                    </button>
                                                @endif
                                            </form>

                                            {{-- Extension blocked warning (inline, shown below action buttons) --}}
                                            @if($blocked && $nextBook)
                                                <div class="absolute right-4 top-full mt-1 z-10 bg-red-50 border border-red-200 text-red-800 text-[0.75rem] rounded-lg px-3 py-2 shadow-md w-64 text-left"
                                                     x-data x-init="setTimeout(() => $el.remove(), 8000)">
                                                    <i class="bi bi-exclamation-triangle-fill mr-1 text-red-500"></i>
                                                    <strong>Extension impossible.</strong><br>
                                                    Room {{ $booking->room?->room_number }} is reserved by another guest from
                                                    {{ $nextBook->check_in_date?->format('M d') }}.
                                                    Use <strong>Relocate</strong> to suggest an alternative room.
                                                </div>
                                            @endif

                                            {{-- Extend Stay inline form (shown only if not blocked) --}}
                                            @if(! $blocked)
                                                <div x-show="showExtend" x-cloak
                                                    class="absolute right-4 mt-2 z-10 bg-white border border-emerald-200 rounded-xl shadow-xl p-4 w-72"
                                                    style="top: auto;">
                                                    <form action="{{ route('reception.extend-stay', $booking->id) }}" method="POST">
                                                        @csrf
                                                        <p class="text-sm font-semibold text-gray-700 mb-3">
                                                            <i class="bi bi-calendar-plus text-emerald-600 mr-1"></i>
                                                            Extend Stay — Room {{ $booking->room?->room_number ?? '-' }}
                                                        </p>
                                                        @if($nextBook)
                                                            <p class="text-[0.75rem] text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1.5 mb-3">
                                                                <i class="bi bi-info-circle mr-1"></i>
                                                                Max <strong>{{ $maxNights }} night(s)</strong> — next guest arrives {{ $nextBook->check_in_date?->format('M d') }}.
                                                            </p>
                                                        @endif
                                                        <div class="mb-3">
                                                            <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Extra Nights</label>
                                                            <input type="number" name="extra_nights" min="1" max="{{ $maxNights }}" value="1" required
                                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-center focus:border-emerald-500 focus:ring-1 focus:ring-emerald-200 outline-none">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="block text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Payment Method</label>
                                                            <select name="payment_method" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-emerald-500 outline-none">
                                                                <option value="cash">Cash</option>
                                                                <option value="khqr">KHQR</option>
                                                            </select>
                                                        </div>
                                                        <p class="text-xs text-gray-400 mb-3">Rate: ${{ number_format($booking->room?->roomType?->price_per_night ?? 0, 2) }}/night</p>
                                                        <div class="flex gap-2">
                                                            <button type="submit" onclick="return confirm('Extend stay and collect payment?')"
                                                                class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 rounded-lg text-sm transition-colors">
                                                                Confirm
                                                            </button>
                                                            <button type="button" @click="showExtend = false"
                                                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold py-2 rounded-lg text-sm transition-colors">
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
                    <p class="text-gray-500 text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">No guests currently staying in the hotel.</p>
                @endif
            </div>

            {{-- ==========================================
                 TAB 3 CONTENT: RECENT HISTORY (LAST 14 DAYS)
                 ========================================== --}}
            <div x-show="activeTab === 'history'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-playfair text-[1.25rem] font-bold text-hotel-dark flex items-center">
                        <i class="bi bi-clock-history text-purple-500 mr-2"></i> Recent History
                    </h3>
                    <span class="text-xs font-normal text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Showing Last 14 Days</span>
                </div>
                @if($recentHistory->count() > 0)
                    <div class="overflow-x-auto max-h-[600px] overflow-y-auto border border-gray-100 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-5 py-4 font-semibold rounded-tl-xl rounded-bl-xl bg-gray-50">Ref</th>
                                    <th class="px-5 py-4 font-semibold bg-gray-50">Guest Name</th>
                                    <th class="px-5 py-4 font-semibold bg-gray-50">Room</th>
                                    <th class="px-5 py-4 font-semibold bg-gray-50">Dates</th>
                                    <th class="px-5 py-4 font-semibold rounded-tr-xl rounded-br-xl bg-gray-50">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($recentHistory as $booking)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <strong class="font-playfair text-hotel-gold text-lg">{{ $booking->referenceNumber() }}</strong>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800 text-[0.95rem]">
                                            {{ $booking->guest?->full_name ?? 'Walk-in Guest' }}
                                        </div>
                                        <div class="text-gray-500 text-[0.8rem] mt-0.5">
                                            {{ $booking->guest?->phones?->first()?->phone_number ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-gray-800 font-medium text-[0.95rem]">Room {{ $booking->room?->room_number ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-700 text-[0.85rem] whitespace-nowrap">
                                        {{ $booking->check_in_date?->format('M d') }} - {{ $booking->check_out_date?->format('M d, Y') }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        @if($booking->isCancelled())
                                            <span class="bg-red-100 text-red-800 text-[0.75rem] font-bold px-3 py-1 rounded-full">Cancelled</span>
                                        @else
                                            <span class="bg-gray-200 text-gray-800 text-[0.75rem] font-bold px-3 py-1 rounded-full">Checked Out</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">No recent history available.</p>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
