@extends('layouts.admin')

@section('title', 'Guest Profile - Admin Dashboard')

@section('content')

<div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-12 mb-10 text-white">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex items-center gap-4 mb-2">
            <div class="w-16 h-16 rounded-full bg-white/10 border-2 border-white/20 flex items-center justify-center text-2xl font-bold">
                {{ strtoupper(substr($guest->full_name, 0, 1)) }}
            </div>
            <div>
                <h1 class="font-playfair text-3xl md:text-[2.2rem] font-bold">{{ $guest->full_name }}</h1>
                <p class="text-white/70 text-[0.95rem]">
                    @if($guest->hasAccount())
                        Registered Guest Account
                    @else
                        Walk-in / Phone Guest
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 md:px-6 pb-12">

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.guests.index') }}" class="text-hotel-gold hover:text-hotel-gold/80 flex items-center font-medium transition-colors">
            <i class="bi bi-arrow-left mr-2"></i> Back to Guests
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Information Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Profile Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <div class="text-[0.75rem] font-semibold text-gray-400 uppercase tracking-wider">Full Name</div>
                        <div class="text-gray-800 font-medium">{{ $guest->full_name }}</div>
                    </div>
                    <div>
                        <div class="text-[0.75rem] font-semibold text-gray-400 uppercase tracking-wider">Email Address</div>
                        <div class="text-gray-800 font-medium">
                            @if($guest->guestAuth)
                                <a href="mailto:{{ $guest->guestAuth->email }}" class="text-blue-600 hover:underline">{{ $guest->guestAuth->email }}</a>
                            @else
                                <span class="text-gray-400 italic">No email linked</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-[0.75rem] font-semibold text-gray-400 uppercase tracking-wider">Phone Numbers</div>
                        <div class="text-gray-800 font-medium">
                            @forelse($guest->phones as $phone)
                                <div><a href="tel:{{ $phone->phone_number }}" class="text-blue-600 hover:underline">{{ $phone->phone_number }}</a></div>
                            @empty
                                <span class="text-gray-400 italic">No phone linked</span>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <div class="text-[0.75rem] font-semibold text-gray-400 uppercase tracking-wider">Gender & Nationality</div>
                        <div class="text-gray-800 font-medium">
                            {{ $guest->displayGender() }}
                            @if($guest->nationality)
                                &bull; {{ $guest->nationality }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-[0.75rem] font-semibold text-gray-400 uppercase tracking-wider">Joined Date</div>
                        <div class="text-gray-800 font-medium">{{ $guest->created_at->format('F j, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Booking History --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.06)] overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Booking History</h3>
                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2.5 py-1 rounded-full">{{ $guest->bookings->count() }} Bookings</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 text-[0.8rem] uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Booking Ref</th>
                                <th class="px-5 py-4 font-semibold">Dates</th>
                                <th class="px-5 py-4 font-semibold">Room</th>
                                <th class="px-5 py-4 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($guest->bookings as $booking)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <strong class="font-playfair text-hotel-gold text-lg">{{ $booking->referenceNumber() }}</strong>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-gray-800 text-[0.95rem]"><strong>In:</strong> {{ $booking->check_in_date?->format('M d, Y') }}</div>
                                    <div class="text-gray-800 text-[0.95rem] mt-0.5"><strong>Out:</strong> {{ $booking->check_out_date?->format('M d, Y') }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-gray-800 font-medium text-[0.95rem]">{{ $booking->displayRooms() ?: 'N/A' }}</div>
                                    <div class="text-gray-500 text-[0.8rem] mt-0.5">Rooms: {{ $booking->bookingRooms->map(fn($br) => $br->room?->room_number ?? 'TBA')->implode(', ') ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'pending'     => 'bg-yellow-100 text-yellow-800',
                                            'booked'      => 'bg-blue-100 text-blue-800',
                                            'checked-in'  => 'bg-green-100 text-green-800',
                                            'checked-out' => 'bg-gray-100 text-gray-800',
                                            'cancelled'   => 'bg-red-100 text-red-800',
                                            'no_show'     => 'bg-orange-100 text-orange-800',
                                        ];
                                        $statusLabels = [
                                            'pending'     => 'Pending',
                                            'booked'      => 'Booked',
                                            'checked-in'  => 'Checked In',
                                            'checked-out' => 'Checked Out',
                                            'cancelled'   => 'Cancelled',
                                            'no_show'     => 'No Show',
                                        ];
                                        $sc = $statusColors[$booking->booking_status] ?? 'bg-gray-100 text-gray-800';
                                        $sl = $statusLabels[$booking->booking_status] ?? ucfirst($booking->booking_status);
                                    @endphp
                                    <span class="{{ $sc }} text-[0.75rem] font-bold px-3 py-1 rounded-full">{{ $sl }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">This guest has no bookings yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
