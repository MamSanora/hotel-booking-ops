@extends('layouts.public')

@section('title', 'Payment — ' . $booking->referenceNumber())

@section('content')

<div class="min-h-[85vh] bg-[#f0f2f5] py-10 px-4">
    <div class="max-w-5xl w-full mx-auto">

        {{-- ── Top Bar ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-7">
            <div class="flex items-center gap-3">
                <a href="{{ route('guest.booking.show', $booking) }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-gray-800 transition-colors text-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 leading-snug">Complete Payment — KHQR / ABA Pay</h1>
                    <p class="text-xs text-gray-500">Scan the QR code below to transfer the exact amount</p>
                </div>
            </div>
            <div class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                Ref:&nbsp;<strong class="text-gray-900">{{ $booking->referenceNumber() }}</strong>
            </div>
        </div>

        {{-- ── Main Grid ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- ═══════════════════════════════════
                 LEFT — QR Card
                 ═══════════════════════════════════ --}}
            <div class="lg:col-span-5 flex flex-col items-center">

                {{-- KHQR Card --}}
                <div class="w-full max-w-[340px] bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.10)] border border-gray-200/60 overflow-hidden">

                    {{-- KHQR Red Header --}}
                    <div class="bg-gradient-to-br from-[#D62B2B] to-[#a01f1f] px-6 pt-5 pb-8 text-white"
                         style="clip-path: polygon(0 0, 100% 0, 100% 65%, 86% 100%, 0 100%);">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="bg-white rounded px-2 py-0.5">
                                    <span class="text-[#D62B2B] font-black text-[0.95rem] tracking-wide">KHQR</span>
                                </div>
                                <span class="text-white/85 text-[0.72rem] font-semibold tracking-wide">& ABA Pay</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-white/80 text-[10px] font-bold tracking-widest uppercase bg-white/20 px-2 py-0.5 rounded">
                                <i class="bi bi-shield-check"></i> Secure
                            </div>
                        </div>
                    </div>

                    {{-- Amount + Room --}}
                    <div class="px-6 pt-4 pb-2">
                        <div class="flex items-end justify-between">
                            <div>
                                <div class="text-[10px] font-semibold tracking-widest text-gray-400 uppercase">Amount Due</div>
                                <div class="flex items-baseline gap-1.5 mt-0.5">
                                    <span class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($transaction->amount_paid, 2) }}</span>
                                    <span class="text-xs font-bold text-gray-500 uppercase">USD</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-semibold tracking-widest text-gray-400 uppercase">Room</div>
                                <div class="text-sm font-bold text-gray-800 mt-0.5">{{ $booking->room?->displayType() ?? 'Reserved' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Perforated divider --}}
                    <div class="relative my-3 flex items-center">
                        <div class="absolute -left-3 w-6 h-6 rounded-full bg-[#f0f2f5] border border-gray-200/80 z-10"></div>
                        <div class="w-full border-b-2 border-dashed border-gray-200 mx-4"></div>
                        <div class="absolute -right-3 w-6 h-6 rounded-full bg-[#f0f2f5] border border-gray-200/80 z-10"></div>
                    </div>

                    {{-- QR Image --}}
                    <div class="px-6 pb-6 pt-1 flex flex-col items-center">
                        <div class="relative bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                            <div class="w-[200px] h-[200px] flex items-center justify-center">
                                @if($qrExists)
                                    <img src="{{ $qrImagePath }}" alt="KHQR Payment QR Code" class="w-full h-full object-contain">
                                @else
                                    <div class="text-center px-4">
                                        <i class="bi bi-qr-code text-5xl text-gray-300 block mb-2"></i>
                                        <p class="text-gray-400 text-xs font-medium">
                                            QR image not found for<br>
                                            <strong class="text-gray-600">${{ number_format($transaction->amount_paid, 2) }}</strong>
                                        </p>
                                        <p class="text-red-500 text-[10px] mt-1">Please contact reception.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-1.5 text-gray-500 text-[11px] font-medium">
                            <span class="w-2 h-2 rounded-full bg-[#D62B2B]"></span>
                            <span>Powered by <strong class="text-gray-700">Bakong · National Bank of Cambodia</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Awaiting confirmation notice --}}
                <div class="mt-4 w-full max-w-[340px] bg-white border border-gray-200/80 rounded-xl py-3 px-4 shadow-sm flex items-center justify-between text-xs font-medium text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        Awaiting receptionist confirmation…
                    </div>
                    <i class="bi bi-bell text-hotel-gold text-sm"></i>
                </div>

                @if(! app()->isProduction())
                <div class="mt-2 w-full max-w-[340px]">
                    <form method="POST" action="{{ route('payment.simulate', $booking) }}"
                          onsubmit="return confirm('DEMO MODE: Simulate a successful payment?')">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold border border-emerald-300 rounded-xl py-2.5 text-xs transition-all shadow-sm">
                            <i class="bi bi-check-circle-fill"></i> Simulate Successful Payment (Local Demo)
                        </button>
                    </form>
                </div>
                @endif

            </div>

            {{-- ═══════════════════════════════════
                 RIGHT — Instructions & Summary
                 ═══════════════════════════════════ --}}
            <div class="lg:col-span-7 space-y-4">

                {{-- How to Pay --}}
                <div class="bg-white rounded-2xl border border-gray-200/60 p-5 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="bi bi-phone text-[#D62B2B]"></i> How to Pay
                    </h3>
                    <div class="space-y-3">
                        @foreach([
                            'Open <strong>ABA Mobile</strong> or any <strong>Bakong-supported app</strong> (Wing, ACLEDA, etc.).',
                            'Tap <strong>Scan QR</strong> (or the QR icon on the home screen).',
                            'Point your camera at the QR code and verify the amount is <strong>$'. number_format($transaction->amount_paid, 2) .' USD</strong>.',
                            'Confirm the transfer with your PIN or biometrics.',
                            'Show the <strong>transfer confirmation screen</strong> to the receptionist, who will mark your booking as confirmed.',
                        ] as $i => $step)
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-[#D62B2B] text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                            <p class="text-xs text-gray-600 leading-relaxed">{!! $step !!}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Booking Summary --}}
                <div class="bg-white rounded-2xl border border-gray-200/60 p-5 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest mb-4">Booking Summary</h3>
                    <div class="divide-y divide-gray-100 text-[13px]">
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Guest Name</span>
                            <span class="font-semibold text-gray-900">{{ $booking->guest_name }}</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Room Type</span>
                            <span class="font-semibold text-gray-900">{{ $booking->room?->displayType() ?? 'Room' }}</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Check-in</span>
                            <span class="font-semibold text-gray-900">{{ $booking->check_in_date?->format('D, M d, Y') }} (2:00 PM)</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Check-out</span>
                            <span class="font-semibold text-gray-900">{{ $booking->check_out_date?->format('D, M d, Y') }} (12:00 PM)</span>
                        </div>
                        @if($booking->payment_tier < 100)
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Total Room Price</span>
                            <span class="font-semibold text-gray-900">${{ number_format($booking->total_price, 2) }} USD</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Payment Option</span>
                            <span class="font-semibold text-gray-900">{{ $booking->payment_tier }}% Deposit</span>
                        </div>
                        <div class="pt-3 pb-1 flex justify-between items-center border-t border-gray-100">
                            <span class="text-sm font-bold text-gray-800">Deposit Payable Now</span>
                            <span class="text-sm font-bold text-[#D62B2B]">${{ number_format($transaction->amount_paid, 2) }} USD</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center bg-gray-50 px-2.5 rounded-lg mt-1 text-xs">
                            <span class="text-gray-500 font-medium">Balance Due at Check-in</span>
                            <span class="font-bold text-gray-800">${{ number_format($booking->remainingBalance(), 2) }} USD</span>
                        </div>
                        @else
                        <div class="pt-3 pb-1 flex justify-between items-center border-t border-gray-100">
                            <span class="text-sm font-bold text-gray-800">Total Payable (Full)</span>
                            <span class="text-sm font-bold text-[#D62B2B]">${{ number_format($transaction->amount_paid, 2) }} USD</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Security note --}}
                <div class="bg-[#fff5f5] border border-[#fecdcc] rounded-2xl p-4 flex items-start gap-3">
                    <i class="bi bi-shield-lock-fill text-[#D62B2B] text-xl shrink-0 mt-0.5"></i>
                    <div class="text-[0.8rem] text-[#6b1010]">
                        <p class="font-bold mb-0.5">Processed via KHQR — National Bank of Cambodia</p>
                        <p class="text-[#8b2020]">KHQR is the official interoperable QR payment standard regulated by the National Bank of Cambodia. Your banking credentials are never shared with Dara Meas Hotel's servers.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
