@extends('layouts.public')

@section('title', 'My Bookings')

@section('content')

{{-- ==========================================
     DASHBOARD HEADER
     ========================================== --}}
<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-10 lg:py-14 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="flex items-center gap-5">
                <i class="bi bi-person-circle text-5xl lg:text-[3.5rem] text-hotel-gold"></i>
                <div>
                    <h1 class="font-playfair text-2xl lg:text-4xl font-bold mb-1">
                        Welcome back, <span class="text-hotel-gold">{{ Auth::user()->guest?->full_name ?? Auth::user()->email }}</span>!
                    </h1>
                    <p class="text-white/70 text-sm lg:text-base flex items-center gap-2">
                        <i class="bi bi-envelope"></i> {{ Auth::user()->email }}
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('guest.profile.edit') }}" class="inline-flex items-center bg-white/10 hover:bg-white/20 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors border border-white/20">
                    <i class="bi bi-person-gear mr-2"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 py-12">

    {{-- ==========================================
         STATS ROW
         ========================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">

        <!-- Upcoming Stays -->
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] p-6 hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-playfair text-3xl font-bold text-hotel-dark leading-tight">{{ $upcomingBookings->count() }}</div>
                    <div class="text-gray-500 text-[0.75rem] font-bold uppercase tracking-wider mt-1">Upcoming Stays</div>
                </div>
                <div class="w-14 h-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
        </div>

        <!-- Past Stays -->
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] p-6 hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-playfair text-3xl font-bold text-hotel-dark leading-tight">{{ $pastBookings->count() }}</div>
                    <div class="text-gray-500 text-[0.75rem] font-bold uppercase tracking-wider mt-1">Past Stays</div>
                </div>
                <div class="w-14 h-14 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-2xl">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>

        <!-- Currently In Hotel -->
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] p-6 hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-playfair text-3xl font-bold text-hotel-dark leading-tight">
                        {{ $upcomingBookings->where('booking_status', 'checked-in')->count() }}
                    </div>
                    <div class="text-gray-500 text-[0.75rem] font-bold uppercase tracking-wider mt-1">Currently In Hotel</div>
                </div>
                <div class="w-14 h-14 rounded-xl bg-orange-50 text-hotel-gold flex items-center justify-center text-2xl">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>

        <!-- Total Stays -->
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] p-6 hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-center">
                <div>
                    <div class="font-playfair text-3xl font-bold text-hotel-dark leading-tight">
                        {{ $upcomingBookings->count() + $pastBookings->count() }}
                    </div>
                    <div class="text-gray-500 text-[0.75rem] font-bold uppercase tracking-wider mt-1">Total Stays</div>
                </div>
                <div class="w-14 h-14 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-2xl">
                    <i class="bi bi-award"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ==========================================
         UPCOMING BOOKINGS
         ========================================== --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="font-playfair text-2xl font-bold text-hotel-dark flex items-center">
            <i class="bi bi-calendar-week mr-3 text-hotel-gold"></i>Upcoming & Active Bookings
        </h2>
        <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-hotel-dark hover:bg-hotel-accent text-white font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
            <i class="bi bi-plus mr-1"></i>Book a Room
        </a>
    </div>

    @if($upcomingBookings->count() > 0)
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-hotel-dark text-white text-[0.75rem] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Reference</th>
                            <th class="px-5 py-4 font-semibold">Room</th>
                            <th class="px-5 py-4 font-semibold">Check-In</th>
                            <th class="px-5 py-4 font-semibold">Check-Out</th>
                            <th class="px-5 py-4 font-semibold text-center">Nights</th>
                            <th class="px-5 py-4 font-semibold">Total</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0ebe2]">
                        @foreach($upcomingBookings as $booking)
                            @php
                                $statusColors = [
                                    'pending'     => 'bg-yellow-100 text-yellow-800',
                                    'booked'      => 'bg-cyan-100 text-cyan-800',
                                    'checked-in'  => 'bg-green-100 text-green-800',
                                    'checked-out' => 'bg-gray-200 text-gray-800',
                                    'cancelled'   => 'bg-red-100 text-red-800',
                                ];
                                $statusClass  = $statusColors[$booking->booking_status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabels = [
                                    'pending'     => 'Pending',
                                    'booked'      => 'Booked',
                                    'checked-in'  => 'Checked In',
                                    'checked-out' => 'Checked Out',
                                    'cancelled'   => 'Cancelled',
                                ];
                                $statusLabel = $statusLabels[$booking->booking_status] ?? ucfirst($booking->booking_status);
                            @endphp
                            <tr class="hover:bg-[#fdfaf6] transition-colors group">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-playfair font-bold text-hotel-gold text-[1.05rem]">{{ $booking->referenceNumber() }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-[0.9rem] text-hotel-dark">
                                        {{ $booking->room?->displayType() ?? 'Unassigned Room' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-700 whitespace-nowrap">
                                    {{ $booking->check_in_date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-700 whitespace-nowrap">
                                    {{ $booking->check_out_date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 text-[0.95rem] font-semibold text-center text-gray-800">
                                    {{ $booking->nightCount() }}
                                </td>
                                <td class="px-5 py-4 font-bold text-hotel-gold whitespace-nowrap">
                                    ${{ number_format($booking->total_price, 2) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="{{ $statusClass }} text-[0.75rem] font-bold px-3 py-1 rounded-full tracking-wide">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('guest.booking.show', $booking->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-hotel-gold transition-colors" title="View Details">
                                            <i class="bi bi-eye text-lg"></i>
                                        </a>
                                        @if($booking->canCancel())
                                            <form method="POST" action="{{ route('guest.booking.cancel', $booking->id) }}"
                                                  onsubmit="return confirm('Cancel this booking?')" class="inline-block">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Cancel">
                                                    <i class="bi bi-x text-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-hotel-light rounded-2xl text-center py-16 px-6 mb-12">
            <i class="bi bi-calendar-x text-[3.5rem] text-hotel-gold mb-4 inline-block"></i>
            <h5 class="font-bold text-xl text-hotel-dark mb-2">No Upcoming Bookings</h5>
            <p class="text-gray-500 mb-6">You have no active or upcoming reservations.</p>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center bg-hotel-gold hover:bg-hotel-gold-hover text-hotel-dark font-semibold px-6 py-2.5 rounded-lg transition-colors">
                <i class="bi bi-door-open mr-2"></i>Browse Available Rooms
            </a>
        </div>
    @endif

    {{-- ==========================================
         PAST BOOKINGS
         ========================================== --}}
    <h2 class="font-playfair text-2xl font-bold text-hotel-dark flex items-center mb-6">
        <i class="bi bi-clock-history mr-3 text-gray-500"></i>Past Bookings
    </h2>

    @if($pastBookings->count() > 0)
        <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.07)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-hotel-dark text-white text-[0.75rem] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Reference</th>
                            <th class="px-5 py-4 font-semibold">Room</th>
                            <th class="px-5 py-4 font-semibold">Check-In</th>
                            <th class="px-5 py-4 font-semibold">Check-Out</th>
                            <th class="px-5 py-4 font-semibold">Total</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0ebe2]">
                        @foreach($pastBookings as $booking)
                            @php
                                $statusColors = [
                                    'pending'     => 'bg-yellow-100 text-yellow-800',
                                    'booked'      => 'bg-cyan-100 text-cyan-800',
                                    'checked-in'  => 'bg-green-100 text-green-800',
                                    'checked-out' => 'bg-gray-200 text-gray-800',
                                    'cancelled'   => 'bg-red-100 text-red-800',
                                ];
                                $statusClass  = $statusColors[$booking->booking_status] ?? 'bg-gray-100 text-gray-800';
                                $statusLabels = [
                                    'pending'     => 'Pending',
                                    'booked'      => 'Booked',
                                    'checked-in'  => 'Checked In',
                                    'checked-out' => 'Checked Out',
                                    'cancelled'   => 'Cancelled',
                                ];
                                $statusLabel = $statusLabels[$booking->booking_status] ?? ucfirst($booking->booking_status);
                            @endphp
                            <tr class="hover:bg-[#fdfaf6] transition-colors group">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-playfair font-bold text-gray-400 text-[1.05rem]">{{ $booking->referenceNumber() }}</span>
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-600">
                                    {{ $booking->room?->displayType() ?? 'Unassigned Room' }}
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-600 whitespace-nowrap">
                                    {{ $booking->check_in_date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 text-[0.9rem] text-gray-600 whitespace-nowrap">
                                    {{ $booking->check_out_date?->format('M d, Y') }}
                                </td>
                                <td class="px-5 py-4 font-semibold text-gray-800 whitespace-nowrap">
                                    ${{ number_format($booking->total_price, 2) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="{{ $statusClass }} text-[0.75rem] font-bold px-3 py-1 rounded-full tracking-wide">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('guest.booking.show', $booking->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-hotel-gold transition-colors" title="View Details">
                                        <i class="bi bi-eye text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-hotel-light rounded-2xl text-center py-12 px-6">
            <i class="bi bi-calendar2-check text-[3rem] text-gray-400 mb-4 inline-block"></i>
            <h5 class="font-bold text-xl text-hotel-dark mb-2">No Past Stays Yet</h5>
            <p class="text-gray-500">Your completed bookings will appear here.</p>
        </div>
    @endif

</div>

@endsection
