@extends('layouts.public')

@section('title', 'Booking Confirmed!')

@section('content')

<div class="min-h-[80vh] bg-[#f0faf4] flex items-center py-12">
    <div class="container mx-auto px-4">
        
        <div class="bg-white rounded-[20px] shadow-[0_12px_48px_rgba(0,0,0,0.1)] max-w-2xl mx-auto overflow-hidden">

            {{-- ── Confetti Strip ── --}}
            <div class="h-[6px] w-full" style="background: repeating-linear-gradient(90deg, #c8a96e 0px, #c8a96e 20px, #2e7d32 20px, #2e7d32 40px, #1a1a2e 40px, #1a1a2e 60px, #c8a96e 60px);"></div>

            {{-- ── Success Header ── --}}
            <div class="bg-gradient-to-br from-[#1b5e20] to-[#2e7d32] py-12 px-6 md:px-10 text-center">
                <div class="w-20 h-20 rounded-full bg-white/15 flex items-center justify-center mx-auto mb-6 text-[2.5rem] text-white animate-bounce">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h2 class="font-playfair text-white text-3xl md:text-[2rem] font-bold mb-2">Booking Confirmed!</h2>
                <p class="text-white/80 text-[0.95rem] mb-2">Your payment was successful. Your room is reserved.</p>
                <div class="inline-block bg-white/12 border border-white/30 rounded-xl px-8 py-3 mt-4 text-white font-playfair text-[1.4rem] font-bold tracking-[0.08em]">
                    {{ $booking->referenceNumber() }}
                </div>
            </div>

            {{-- ── Booking Details ── --}}
            <div class="px-6 md:px-10 py-8">
                
                <div class="flex justify-between items-center py-3.5 border-b border-[#f0ebe2] text-[0.92rem]">
                    <span class="text-gray-500 font-semibold flex items-center"><i class="bi bi-door-open mr-2 text-hotel-gold"></i>Room</span>
                    <span class="text-hotel-dark font-semibold text-right">
                        {{ $booking->room?->displayType() ?? 'Room' }}
                    </span>
                </div>

                <div class="flex justify-between items-center py-3.5 border-b border-[#f0ebe2] text-[0.92rem]">
                    <span class="text-gray-500 font-semibold flex items-center"><i class="bi bi-person mr-2 text-hotel-gold"></i>Guest Name</span>
                    <span class="text-hotel-dark font-semibold text-right">
                        {{ $booking->guest?->full_name ?? 'Walk-in Guest' }}
                    </span>
                </div>

                <div class="flex justify-between items-center py-3.5 border-b border-[#f0ebe2] text-[0.92rem]">
                    <span class="text-gray-500 font-semibold flex items-center"><i class="bi bi-calendar-check mr-2 text-hotel-gold"></i>Check-In</span>
                    <span class="text-hotel-dark font-semibold text-right">
                        {{ $booking->check_in_date?->format('D, M d, Y') }}
                    </span>
                </div>

                <div class="flex justify-between items-center py-3.5 border-b border-[#f0ebe2] text-[0.92rem]">
                    <span class="text-gray-500 font-semibold flex items-center"><i class="bi bi-calendar-x mr-2 text-hotel-gold"></i>Check-Out</span>
                    <span class="text-hotel-dark font-semibold text-right">
                        {{ $booking->check_out_date?->format('D, M d, Y') }}
                    </span>
                </div>

                <div class="flex justify-between items-center py-3.5 border-b border-[#f0ebe2] text-[0.92rem]">
                    <span class="text-gray-500 font-semibold flex items-center"><i class="bi bi-moon mr-2 text-hotel-gold"></i>Duration</span>
                    <span class="text-hotel-dark font-semibold text-right">{{ $booking->nightCount() }} Night(s)</span>
                </div>

                <div class="flex justify-between items-center py-3.5 border-b border-[#f0ebe2] text-[0.92rem]">
                    <span class="text-gray-500 font-semibold flex items-center"><i class="bi bi-credit-card mr-2 text-hotel-gold"></i>Payment Method</span>
                    <span class="text-hotel-dark font-semibold text-right">
                        @php
                            $method = $booking->transactions->last()?->payment_method ?? 'khqr';
                        @endphp
                        @if($method === 'aba_payway')
                            ABA PayWay
                        @elseif($method === 'khqr')
                            KHQR &bull; Bakong
                        @else
                            {{ ucfirst($method) }}
                        @endif
                    </span>
                </div>

                <div class="flex justify-between items-center py-4 text-[0.92rem]">
                    @php $totalPaid = $booking->transactions->whereIn('payment_status', ['full', 'half'])->sum('amount_paid'); @endphp
                    <span class="text-hotel-dark font-bold text-base">Total Paid</span>
                    <span class="font-playfair text-[1.5rem] text-[#2e7d32] font-bold text-right">${{ number_format($totalPaid, 2) }}</span>
                </div>

            </div>

            {{-- ── Notice ── --}}
            <div class="bg-[#e8f5e9] border border-[#a5d6a7] rounded-xl p-4 mx-6 md:mx-10 mb-6 flex items-start gap-4">
                <i class="bi bi-info-circle-fill text-[#2e7d32] text-xl mt-0.5"></i>
                <p class="text-[#1b5e20] text-[0.88rem] leading-relaxed mb-0">
                    <strong class="font-bold">Check-in is from 2:00 PM onwards.</strong>
                    Please present this booking reference at the front desk.
                    For any assistance, call us at <strong class="font-bold">+855 23 456 789</strong>.
                </p>
            </div>

            {{-- ── Actions ── --}}
            <div class="px-6 md:px-10 pb-8 flex flex-wrap gap-4 justify-center md:justify-start">
                @auth
                    <a href="{{ route('guest.dashboard') }}" class="inline-flex items-center justify-center bg-hotel-dark hover:bg-[#0f0f1e] text-white font-semibold rounded-xl px-7 py-3 transition-transform duration-200 hover:-translate-y-0.5 w-full sm:w-auto">
                        <i class="bi bi-calendar-check mr-2"></i>View My Bookings
                    </a>
                @endauth
                <a href="{{ route('rooms.index') }}" class="inline-flex items-center justify-center border-[1.5px] border-hotel-gold text-hotel-gold hover:bg-hotel-gold hover:text-white font-semibold rounded-xl px-7 py-3 transition-colors duration-200 w-full sm:w-auto">
                    <i class="bi bi-door-open mr-2"></i>Browse More Rooms
                </a>
            </div>

            <div class="px-6 pb-6 pt-2 text-center text-gray-500 text-[0.78rem]">
                <i class="bi bi-shield-check mr-1 text-green-500"></i>
                Powered by {{ $method === 'aba_payway' ? 'ABA PayWay' : 'Bakong • NBC' }} &nbsp;&middot;&nbsp; Dara Meas Hotel, Phnom Penh
            </div>

        </div>
        
    </div>
</div>

@endsection
