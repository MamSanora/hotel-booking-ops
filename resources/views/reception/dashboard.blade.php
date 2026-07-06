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
             TODAY'S ARRIVALS
             ========================================== --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8">
            <h3 class="font-playfair text-[1.4rem] font-bold text-hotel-dark pb-3 border-b-2 border-gray-100 mb-6 flex items-center">
                <i class="bi bi-box-arrow-in-right text-green-500 mr-3"></i>
                Today's Arrivals ({{ $todayArrivals->count() }})
            </h3>
            
            @if($todayArrivals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-4 font-semibold rounded-tl-xl rounded-bl-xl">Ref</th>
                                <th class="px-5 py-4 font-semibold">Guest Name</th>
                                <th class="px-5 py-4 font-semibold">Room</th>
                                <th class="px-5 py-4 font-semibold">Payment</th>
                                <th class="px-5 py-4 font-semibold rounded-tr-xl rounded-br-xl text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($todayArrivals as $booking)
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
                                    <form action="{{ route('reception.checkin', $booking->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Check in this guest?')" class="inline-flex items-center bg-green-100 hover:bg-green-200 text-green-700 font-semibold px-3 py-2 rounded-lg text-sm transition-colors border border-green-200">
                                            <i class="bi bi-check2-square mr-1"></i>Check In
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">No arrivals scheduled for today.</p>
            @endif
        </div>

        {{-- ==========================================
             TODAY'S DEPARTURES
             ========================================== --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8">
            <h3 class="font-playfair text-[1.4rem] font-bold text-hotel-dark pb-3 border-b-2 border-gray-100 mb-6 flex items-center">
                <i class="bi bi-box-arrow-right text-yellow-500 mr-3"></i>
                Today's Departures ({{ $todayDepartures->count() }})
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
             IN-HOUSE GUESTS
             ========================================== --}}
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 md:p-8">
            <h3 class="font-playfair text-[1.4rem] font-bold text-hotel-dark pb-3 border-b-2 border-gray-100 mb-6 flex items-center">
                <i class="bi bi-house-door text-blue-500 mr-3"></i>
                In-House Guests ({{ $inHouseGuests->count() }})
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
                                <td class="px-5 py-4 whitespace-nowrap text-right">
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

    </div>
</div>

@endsection
