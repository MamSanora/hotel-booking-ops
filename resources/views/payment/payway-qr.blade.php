@extends('layouts.public')

@section('title', 'ABA PayWay Payment — ' . $booking->referenceNumber())

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
                    <h1 class="text-lg font-bold text-gray-900 leading-snug">Complete ABA PayWay Payment</h1>
                    <p class="text-xs text-gray-500">Scan with ABA Mobile to pay instantly &amp; securely</p>
                </div>
            </div>
            <div class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Ref:&nbsp;<strong class="text-gray-900">{{ $booking->referenceNumber() }}</strong>
            </div>
        </div>

        {{-- ── Main Grid: 5/12 left | 7/12 right ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- ═══════════════════════════
                 LEFT — ABA QR Card + Status
                 ═══════════════════════════ --}}
            <div class="lg:col-span-5 flex flex-col items-center">

                {{-- ABA PayWay Card --}}
                <div class="w-full max-w-[340px] bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.10)] border border-gray-200/60 overflow-hidden">

                    {{-- ABA Blue header --}}
                    <div class="bg-[#004B87] px-6 pt-5 pb-8 text-white"
                         style="clip-path: polygon(0 0, 100% 0, 100% 65%, 86% 100%, 0 100%);">
                        <div class="flex items-center justify-between">
                            {{-- ABA Logo (text fallback — replace with real logo if available) --}}
                            <div class="flex items-center gap-2">
                                <div class="bg-white rounded px-2 py-0.5">
                                    <span class="text-[#004B87] font-black text-[1rem] tracking-wide">ABA</span>
                                </div>
                                <span class="text-white/80 text-[0.7rem] font-semibold tracking-wide">PayWay</span>
                            </div>
                            <span class="text-[10px] font-bold tracking-widest uppercase bg-white/20 px-2 py-0.5 rounded">Secure Pay</span>
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
                                    <div class="text-gray-400 text-xs font-medium text-center px-4">
                                        <i class="bi bi-qr-code text-5xl text-gray-300 block mb-2"></i>
                                        QR code unavailable.<br>Use the ABA deeplink below.
                                    </div>
                                @endif
                            </div>
                            {{-- ABA logo in centre of QR --}}
                            <div class="absolute inset-0 m-auto w-10 h-10 bg-white rounded-full shadow-[0_2px_8px_rgba(0,0,0,0.18)] border-2 border-white flex items-center justify-center pointer-events-none z-10">
                                <span class="text-[#004B87] font-black text-[0.65rem] leading-none">ABA</span>
                            </div>
                        </div>

                        {{-- ABA Mobile deeplink — mobile only --}}
                        @if(!empty($paymentData['abapay_deeplink']))
                        <a href="{{ $paymentData['abapay_deeplink'] }}"
                           class="mt-4 w-full flex items-center justify-center gap-2 bg-[#004B87] hover:bg-[#003a6a] text-white font-bold rounded-xl px-6 py-2.5 transition-all text-xs md:hidden">
                            <i class="bi bi-phone"></i> Open in ABA Mobile
                        </a>
                        @endif

                        <div class="mt-4 flex items-center gap-1.5 text-gray-500 text-[11px] font-medium">
                            <span class="w-2 h-2 rounded-full bg-[#004B87]"></span>
                            <span>Powered by <strong class="text-gray-700">ABA PayWay &bull; ABA Bank</strong></span>
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
                        Waiting for payment…
                    </div>
                    <div class="flex items-center gap-1 text-gray-500 font-semibold">
                        <i class="bi bi-clock text-[11px]"></i>
                        <span id="countdown">15:00</span>
                    </div>
                </div>

                {{-- Sandbox helper note --}}
                @if(! app()->isProduction())
                <div class="mt-3 w-full max-w-[340px] bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-[11px] text-amber-800">
                    <p class="font-bold mb-1"><i class="bi bi-info-circle mr-1"></i>Sandbox Testing</p>
                    <p>This transaction is now registered in your <strong>ABA PayWay Sandbox Dashboard</strong>.
                    Go to the dashboard and click <strong>"Simulate Payment"</strong> next to tran_id
                    <code class="bg-amber-100 px-1 rounded">{{ $paymentData['transaction_id'] }}</code>.</p>
                </div>

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

            {{-- ═══════════════════════════
                 RIGHT — Info Panels
                 ═══════════════════════════ --}}
            <div class="lg:col-span-7 space-y-4">

                {{-- How to Pay --}}
                <div class="bg-white rounded-2xl border border-gray-200/60 p-5 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="bi bi-phone text-[#004B87]"></i> How to Pay with ABA Mobile
                    </h3>
                    <div class="space-y-3">
                        @foreach([
                            'Open <strong>ABA Mobile</strong> on your phone.',
                            'Tap <strong>Scan QR</strong> (or the QR icon on the home screen).',
                            'Point your camera at the QR code and verify the amount is <strong>$'. number_format($transaction->amount_paid, 2) .' USD</strong>.',
                            'Confirm with your PIN or biometrics. This page will <strong>automatically update</strong> once paid.',
                        ] as $i => $step)
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 w-5 h-5 rounded-full bg-[#004B87] text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                            <p class="text-xs text-gray-600 leading-relaxed">{!! $step !!}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Transaction Details --}}
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
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">ABA Tran ID</span>
                            <span class="font-mono text-[11px] text-gray-700 bg-gray-50 px-2 py-0.5 rounded">{{ $paymentData['transaction_id'] }}</span>
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
                            <span class="text-sm font-bold text-[#004B87]">${{ number_format($transaction->amount_paid, 2) }} USD</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center bg-gray-50 px-2.5 rounded-lg mt-1 text-xs">
                            <span class="text-gray-500 font-medium">Balance Due at Check-in</span>
                            <span class="font-bold text-gray-800">${{ number_format($booking->remainingBalance(), 2) }} USD</span>
                        </div>
                        @else
                        <div class="pt-3 pb-1 flex justify-between items-center border-t border-gray-100">
                            <span class="text-sm font-bold text-gray-800">Total Payable (Full)</span>
                            <span class="text-sm font-bold text-[#004B87]">${{ number_format($transaction->amount_paid, 2) }} USD</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Security note --}}
                <div class="bg-[#eef4fb] border border-[#bdd4ec] rounded-2xl p-4 flex items-start gap-3">
                    <i class="bi bi-shield-lock-fill text-[#004B87] text-xl shrink-0 mt-0.5"></i>
                    <div class="text-[0.8rem] text-[#1a3a5c]">
                        <p class="font-bold mb-0.5">Secured by ABA PayWay</p>
                        <p class="text-[#2a5580]">Your payment is processed entirely through ABA Bank's secure gateway. Your card details and banking credentials are never stored on Dara Meas Hotel's servers.</p>
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

    // Poll for payment status every 5 s (reuses the existing KHQR check-status endpoint)
    const bookingId = {{ $booking->id }};
    const checkInterval = setInterval(async () => {
        try {
            const res = await fetch(`/payment/${bookingId}/check-status`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.paid && data.redirect) {
                clearInterval(checkInterval);
                clearInterval(timer);
                window.location.href = data.redirect;
            }
        } catch (e) { /* silently ignore */ }
    }, 5000);
})();
</script>
@endpush
