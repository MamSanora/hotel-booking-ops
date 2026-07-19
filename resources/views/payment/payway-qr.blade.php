@extends('layouts.public')

@section('title', 'KHQR Payment — ' . $booking->referenceNumber())

@section('content')

<div class="min-h-[85vh] bg-[#f0f2f5] py-10 px-4">
    <div class="max-w-5xl w-full mx-auto">

        {{-- ── Top Bar: Back + Title + Ref ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-7">
            <div class="flex items-center gap-3">
                <a href="{{ route('guest.booking.show', $booking) }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-gray-800 transition-colors text-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 leading-snug">Complete KHQR Payment</h1>
                    <p class="text-xs text-gray-500">Scan &amp; pay instantly with any Cambodian banking app</p>
                </div>
            </div>
            <div class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Ref: &nbsp;<strong class="text-gray-900">{{ $booking->referenceNumber() }}</strong>
            </div>
        </div>

        {{-- ── Main Grid: 5/12 left | 7/12 right ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- ═══════════════════════════
                 LEFT — KHQR Card + Status
                 ═══════════════════════════ --}}
            <div class="lg:col-span-5 flex flex-col items-center">

                {{-- KHQR Card --}}
                <div class="w-full max-w-[340px] bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.10)] border border-gray-200/60 overflow-hidden">

                    {{-- Red header with slanted bottom-right cut --}}
                    <div class="bg-[#E1232C] px-6 pt-5 pb-8 text-white"
                         style="clip-path: polygon(0 0, 100% 0, 100% 65%, 86% 100%, 0 100%);">
                        <div class="flex items-center justify-between">
                            <img src="/images/khqr-logo-white.svg"
                                 alt="KHQR"
                                 style="height:26px;width:auto;display:block;">
                            <span class="text-[10px] font-bold tracking-widest uppercase bg-white/20 px-2 py-0.5 rounded">Tag 29</span>
                        </div>
                    </div>

                    {{-- Amount + Room --}}
                    <div class="px-6 pt-4 pb-2">
                        <div class="flex items-end justify-between">
                            <div>
                                <div class="text-[10px] font-semibold tracking-widest text-gray-400 uppercase">Amount Due</div>
                                <div class="flex items-baseline gap-1.5 mt-0.5">
                                    <span class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($transaction->amount_paid, 2) }}</span>
                                    <span class="text-xs font-bold text-gray-500 uppercase">{{ $paymentData['currency'] ?? 'USD' }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-semibold tracking-widest text-gray-400 uppercase">Room</div>
                                <div class="text-sm font-bold text-gray-800 mt-0.5">{{ $booking->room?->displayType() ?? 'Reserved' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Ticket perforated divider --}}
                    <div class="relative my-3 flex items-center">
                        <div class="absolute -left-3 w-6 h-6 rounded-full bg-[#f0f2f5] border border-gray-200/80 z-10"></div>
                        <div class="w-full border-b-2 border-dashed border-gray-200 mx-4"></div>
                        <div class="absolute -right-3 w-6 h-6 rounded-full bg-[#f0f2f5] border border-gray-200/80 z-10"></div>
                    </div>

                    {{-- QR Code --}}
                    <div class="px-6 pb-6 pt-1 flex flex-col items-center">
                        <div class="relative bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                            <div class="w-[200px] h-[200px] flex items-center justify-center">
                                @if(!empty($paymentData['qr_image']))
                                    <img src="{{ $paymentData['qr_image'] }}" alt="ABA PayWay QR Code" class="w-full h-full object-contain">
                                @else
                                    <div id="khqr-fallback" class="text-gray-400 text-xs font-medium text-center px-4">
                                        <i class="bi bi-qr-code text-5xl text-gray-300 block mb-2"></i>
                                        QR code unavailable.<br>Please use the deeplink below.
                                    </div>
                                @endif
                            </div>
                            {{-- Bakong centre emblem --}}
                            <div class="absolute inset-0 m-auto w-11 h-11 bg-white rounded-full shadow-[0_2px_8px_rgba(0,0,0,0.16)] border-2 border-white flex items-center justify-center pointer-events-none z-10">
                                <img src="/images/bakong-emblem.svg" alt="Bakong" class="w-7 h-7 object-contain">
                            </div>
                        </div>

                        {{-- ABA deeplink — mobile only --}}
                        @if(!empty($paymentData['abapay_deeplink']))
                        <a href="{{ $paymentData['abapay_deeplink'] }}"
                           class="mt-4 w-full flex items-center justify-center gap-2 bg-[#004B87] hover:bg-[#003a6a] text-white font-bold rounded-xl px-6 py-2.5 transition-all text-xs md:hidden">
                            <i class="bi bi-phone"></i> Open in ABA Mobile
                        </a>
                        @endif

                        <div class="mt-4 flex items-center gap-1.5 text-gray-500 text-[11px] font-medium">
                            <span class="w-2 h-2 rounded-full bg-[#E1232C]"></span>
                            <span>Supported by <strong class="text-gray-700">Bakong &bull; NBC</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Status + Countdown bar --}}
                <div id="payment-status"
                     class="mt-4 w-full max-w-[340px] bg-white border border-gray-200/80 rounded-xl py-2.5 px-4 shadow-sm flex items-center justify-between text-xs font-medium text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        Waiting for payment...
                    </div>
                    <div class="flex items-center gap-1 text-gray-500 font-semibold">
                        <i class="bi bi-clock text-[11px]"></i>
                        <span id="countdown">15:00</span>
                    </div>
                </div>

                {{-- Local demo simulate button --}}
                @if(! app()->isProduction())
                <div class="mt-3 w-full max-w-[340px]">
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

            {{-- ═══════════════════════════
                 RIGHT — Info Panels
                 ═══════════════════════════ --}}
            <div class="lg:col-span-7 space-y-4">

                {{-- Supported Banking Apps --}}
                <div class="bg-white rounded-2xl border border-gray-200/60 p-5 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest mb-1 flex items-center gap-2">
                        <i class="bi bi-phone text-[#E1232C]"></i> Supported Banking Apps
                    </h3>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                        Open any banking app in Cambodia that supports <strong class="text-gray-700">KHQR / Bakong</strong> to scan and pay instantly with zero transfer fees.
                    </p>
                    <div class="grid grid-cols-4 gap-3">
                        @foreach([
                            ['src' => '/images/aba-logo.webp',       'label' => 'ABA Mobile'],
                            ['src' => '/images/wing-logo.jpg',       'label' => 'Wing Bank'],
                            ['src' => '/images/acleda-logo.jpg',     'label' => 'ACLEDA'],
                            ['src' => '/images/bakong-app-logo.png', 'label' => 'Bakong'],
                        ] as $app)
                        <div class="flex flex-col items-center gap-1.5">
                            <div class="w-12 h-12 rounded-xl overflow-hidden shadow-sm border border-gray-100">
                                <img src="{{ $app['src'] }}" alt="{{ $app['label'] }}" class="w-full h-full object-cover">
                            </div>
                            <span class="text-[10px] font-semibold text-gray-600 text-center leading-tight">{{ $app['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Quick Payment Steps --}}
                <div class="bg-white rounded-2xl border border-gray-200/60 p-5 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="bi bi-shield-check text-[#E1232C]"></i> Quick Payment Steps
                    </h3>
                    <div class="space-y-3">
                        @foreach([
                            'Open <strong>ABA Mobile, Wing Bank, ACLEDA</strong> or your preferred banking app.',
                            'Tap <strong>KHQR / Scan QR</strong> and point your camera at the code.',
                            'Verify the amount is <strong>$'. number_format($transaction->amount_paid, 2) .' USD</strong>.',
                            'Once paid, this page will <strong>automatically redirect</strong> to your receipt.',
                        ] as $i => $step)
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-[#E1232C] text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
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
                        <div class="pt-3 pb-1 flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-800">Total Payable</span>
                            <span class="text-sm font-bold text-[#E1232C]">${{ number_format($transaction->amount_paid, 2) }} USD</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    let seconds = 15 * 60;
    const countdownEl = document.getElementById('countdown');

    const timer = setInterval(() => {
        seconds--;
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        countdownEl.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        if (seconds <= 0) {
            clearInterval(timer);
            countdownEl.textContent = 'Expired';
            countdownEl.classList.add('text-red-500');
        }
    }, 1000);

    // Poll for payment status every 5 s
    const bookingId = {{ $booking->id }};
    const checkInterval = setInterval(async () => {
        try {
            const res = await fetch(`/payment/${bookingId}/check-status`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.status === 'paid' && data.redirect) {
                clearInterval(checkInterval);
                clearInterval(timer);
                window.location.href = data.redirect;
            }
        } catch (e) { /* silently ignore */ }
    }, 5000);
})();
</script>
@endpush

