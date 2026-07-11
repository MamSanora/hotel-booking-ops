@extends('layouts.public')

@section('title', 'Pay with ABA PayWay — ' . $booking->referenceNumber())

@section('content')

<div class="min-h-[80vh] bg-hotel-light flex items-center py-12">
    <div class="container mx-auto px-4">

        <div class="bg-white rounded-[20px] shadow-[0_12px_48px_rgba(0,0,0,0.12)] max-w-3xl mx-auto overflow-hidden">

            {{-- ── Header ── --}}
            <div class="bg-gradient-to-br from-hotel-dark to-hotel-accent py-8 px-6 md:px-10 text-center">
                <div class="inline-flex items-center gap-2 bg-[#fef0e6] border border-[#f0d5b0] text-hotel-gold font-bold text-[0.85rem] px-4 py-1.5 rounded-full mb-4">
                    <i class="bi bi-credit-card-2-front"></i> ABA PayWay Checkout
                </div>
                <h2 class="font-playfair text-white text-[1.6rem] font-bold mb-1">Complete Your Payment</h2>
                <p class="text-white/65 text-[0.9rem] mb-0">Scan the QR code below with your preferred banking app.</p>
                <div class="inline-block bg-hotel-gold/20 border border-hotel-gold/50 text-hotel-gold font-playfair text-[1.3rem] font-bold px-7 py-2 rounded-full mt-4 tracking-widest">
                    {{ $booking->referenceNumber() }}
                </div>
            </div>

            {{-- ── Booking Summary Strip ── --}}
            <div class="bg-hotel-light px-6 md:px-10 py-5 border-b border-[#ede8df] flex flex-wrap gap-6 items-center">
                <div class="flex items-center gap-2.5 text-[0.88rem]">
                    <i class="bi bi-door-open text-hotel-gold text-base"></i>
                    <div>
                        <span class="text-gray-500 block">Room</span>
                        <strong class="text-hotel-dark">{{ $booking->room?->displayType() ?? 'Room #'.$booking->room_id }}</strong>
                        @if($booking->room)
                            <span class="text-gray-500 text-[0.78rem] ml-1">No. {{ $booking->room->room_number }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2.5 text-[0.88rem]">
                    <i class="bi bi-calendar-check text-hotel-gold text-base"></i>
                    <div>
                        <span class="text-gray-500 block">Check-In</span>
                        <strong class="text-hotel-dark">{{ $booking->check_in_date?->format('M d, Y') }}</strong>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 text-[0.88rem]">
                    <i class="bi bi-calendar-x text-hotel-gold text-base"></i>
                    <div>
                        <span class="text-gray-500 block">Check-Out</span>
                        <strong class="text-hotel-dark">{{ $booking->check_out_date?->format('M d, Y') }}</strong>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 text-[0.88rem]">
                    <i class="bi bi-moon text-hotel-gold text-base"></i>
                    <div>
                        <span class="text-gray-500 block">Nights</span>
                        <strong class="text-hotel-dark">{{ $booking->nightCount() }}</strong>
                    </div>
                </div>
            </div>

            {{-- ── Main Payment Area ── --}}
            <div class="p-8 md:p-10">

                {{-- Amount --}}
                <div class="text-center mb-8">
                    <div class="bg-gradient-to-br from-hotel-gold to-[#b8935a] rounded-xl px-8 py-4 inline-block">
                        <div class="text-white/75 text-[0.78rem] uppercase tracking-widest mb-1">Total Amount Due</div>
                        <div class="text-white font-playfair text-[2.2rem] font-bold leading-tight">${{ number_format($booking->total_price, 2) }} <span class="text-[1rem] font-normal">{{ $paymentData['currency'] }}</span></div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-8 items-start justify-center">

                    {{-- QR Code --}}
                    <div class="flex flex-col items-center gap-4 flex-shrink-0">
                        <p class="text-[0.82rem] text-gray-500 uppercase tracking-wider font-semibold">Scan with your banking app</p>
                        @if($paymentData['qr_image'])
                            <div class="border-4 border-hotel-gold/30 rounded-2xl p-3 shadow-lg bg-white">
                                <img src="{{ $paymentData['qr_image'] }}"
                                     alt="ABA PayWay QR Code"
                                     class="w-56 h-56 block"
                                     id="payway-qr-img">
                            </div>
                        @else
                            <div class="w-56 h-56 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400">
                                <i class="bi bi-qr-code text-5xl"></i>
                            </div>
                        @endif

                        {{-- ABA App deeplink (mobile) --}}
                        @if($paymentData['abapay_deeplink'])
                        <a href="{{ $paymentData['abapay_deeplink'] }}"
                           class="w-full flex items-center justify-center gap-2.5 bg-[#1a3c5e] hover:bg-[#142e4a] text-white font-bold rounded-xl px-6 py-3 transition-all duration-200 text-[0.9rem] md:hidden">
                            <i class="bi bi-phone"></i> Open in ABA Mobile
                        </a>
                        @endif

                        <p class="text-[0.75rem] text-gray-400 text-center max-w-[240px]">
                            Supports ABA Mobile, Wing, ACLEDA, Pi Pay, and all Bakong-connected apps
                        </p>
                    </div>

                    {{-- Right: instructions + mobile app links --}}
                    <div class="flex-1 space-y-4">
                        <h3 class="font-semibold text-hotel-dark text-[1rem]">How to pay:</h3>
                        <ol class="space-y-3 text-[0.88rem] text-gray-600">
                            <li class="flex items-start gap-2.5">
                                <span class="bg-hotel-gold text-white rounded-full w-5 h-5 flex items-center justify-center text-[0.72rem] font-bold flex-shrink-0 mt-0.5">1</span>
                                Open your banking app (ABA Mobile, Wing, ACLEDA, etc.)
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="bg-hotel-gold text-white rounded-full w-5 h-5 flex items-center justify-center text-[0.72rem] font-bold flex-shrink-0 mt-0.5">2</span>
                                Tap <strong>Scan QR</strong> or <strong>Pay</strong>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="bg-hotel-gold text-white rounded-full w-5 h-5 flex items-center justify-center text-[0.72rem] font-bold flex-shrink-0 mt-0.5">3</span>
                                Point your camera at the QR code on screen
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="bg-hotel-gold text-white rounded-full w-5 h-5 flex items-center justify-center text-[0.72rem] font-bold flex-shrink-0 mt-0.5">4</span>
                                Confirm the amount: <strong>${{ number_format($booking->total_price, 2) }}</strong> and complete payment
                            </li>
                        </ol>

                        {{-- Desktop deeplink as app store buttons --}}
                        @if($paymentData['abapay_deeplink'])
                        <div class="pt-2">
                            <p class="text-[0.78rem] text-gray-500 mb-2">On mobile? Open directly in ABA app:</p>
                            <a href="{{ $paymentData['abapay_deeplink'] }}"
                               class="hidden md:inline-flex items-center gap-2.5 bg-[#1a3c5e] hover:bg-[#142e4a] text-white font-bold rounded-xl px-5 py-2.5 transition-all duration-200 text-[0.88rem]">
                                <i class="bi bi-phone"></i> Open in ABA Mobile
                            </a>
                        </div>
                        @endif

                        {{-- Security badge --}}
                        <div class="bg-[#f0f9f0] border border-[#b0d9b0] rounded-xl px-4 py-3 text-[#2a6a2a] text-[0.82rem] flex items-start gap-3 mt-4">
                            <i class="bi bi-shield-lock-fill text-lg flex-shrink-0 mt-0.5"></i>
                            <span>Your payment is secured by ABA PayWay. Card and account details are never stored on our servers.</span>
                        </div>

                        {{-- Transaction ref for support --}}
                        <p class="text-[0.75rem] text-gray-400">
                            Transaction ref: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">{{ $paymentData['transaction_id'] }}</code>
                        </p>
                    </div>
                </div>
            </div>

            {{-- ── Dev / Demo Simulate Button ── --}}
            @if(! app()->isProduction())
            <div class="px-8 md:px-10 pb-8 pt-0 flex flex-wrap gap-4 justify-center bg-white border-t border-gray-100">
                <form method="POST" action="{{ route('payment.simulate', $booking) }}"
                      onsubmit="return confirm('DEMO MODE: Simulate a successful payment?\n\nThis is for testing only — no real money is charged.')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2.5 bg-green-50 hover:bg-green-100 text-green-700 font-bold border-2 border-green-200 hover:border-green-400 rounded-xl px-8 py-3.5 transition-all duration-200">
                        <i class="bi bi-check-circle"></i> Simulate Successful Payment
                    </button>
                </form>
            </div>
            @endif

            {{-- ── Footer Note ── --}}
            <div class="px-6 pb-6 pt-2 text-center text-gray-500 text-[0.78rem] bg-white">
                <i class="bi bi-shield-check mr-1 text-green-500"></i>
                Powered by ABA PayWay.
                &nbsp;&middot;&nbsp; Need help? Call <strong class="text-hotel-dark">+855 23 456 789</strong>
            </div>

        </div>
    </div>
</div>

@endsection
