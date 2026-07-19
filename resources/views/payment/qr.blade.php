@extends('layouts.public')

@section('title', 'KHQR Payment — ' . $booking->referenceNumber())

@push('styles')
<style>
    /* ── KHQR Card Specific Styles ─────────────────────────── */
    .khqr-page-bg {
        background: linear-gradient(135deg, #f0f4f8 0%, #e8edf5 100%);
        min-height: 90vh;
    }

    /* Official KHQR Card */
    .khqr-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.12), 0 4px 16px rgba(0,0,0,0.06);
        overflow: hidden;
        width: 100%;
        max-width: 360px;
    }

    /* Bakong red header */
    .khqr-header {
        background: #D0021B;
        padding: 18px 22px 30px;
        position: relative;
        /* Slanted bottom-right notch — official Bakong card style */
        clip-path: polygon(0 0, 100% 0, 100% 55%, 83% 100%, 0 100%);
    }

    /* KHQR Official Logo Image */
    .khqr-logo-img {
        height: 28px;
        width: auto;
        display: block;
        filter: brightness(0) invert(1); /* Force pure white */
    }

    .khqr-tag-badge {
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 1.5px;
        background: rgba(255,255,255,0.22);
        color: #fff;
        padding: 3px 9px;
        border-radius: 4px;
        text-transform: uppercase;
    }

    /* Dashed ticket separator */
    .ticket-sep {
        position: relative;
        display: flex;
        align-items: center;
        margin: 12px 0;
    }
    .ticket-sep::before,
    .ticket-sep::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        background: #f0f4f8;
        border-radius: 50%;
        top: 50%;
        transform: translateY(-50%);
        border: 1.5px solid #e5e7eb;
    }
    .ticket-sep::before { left: -10px; }
    .ticket-sep::after  { right: -10px; }
    .ticket-sep-line {
        flex: 1;
        border-top: 2px dashed #d1d5db;
        margin: 0 14px;
    }

    /* QR frame */
    .qr-frame {
        padding: 12px;
        border-radius: 14px;
        border: 1.5px solid #e9ecef;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        position: relative;
        display: inline-block;
    }

    /* Center emblem over QR */
    .qr-emblem {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 44px;
        height: 44px;
        background: #fff;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.20);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        z-index: 10;
        pointer-events: none;
    }

    /* Right panel cards */
    .info-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e9ecef;
        padding: 22px 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    /* App icon pill */
    .app-icon {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 12px 8px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e9ecef;
        font-size: 10px;
        font-weight: 700;
        color: #374151;
    }
    .app-icon-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 900;
        color: #fff;
        letter-spacing: 0.5px;
    }

    /* Status pill */
    .status-pill {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px 16px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.04);
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .status-pill.paid {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
        font-weight: 700;
    }
</style>
@endpush

@section('content')

<div class="khqr-page-bg py-10 px-4 flex items-center justify-center">
    <div class="max-w-5xl w-full mx-auto">

        {{-- ── Top Bar ── --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('guest.booking.show', $booking) }}"
                   class="w-9 h-9 rounded-full bg-white border border-gray-200 shadow-sm flex items-center justify-center text-gray-500 hover:text-gray-800 hover:shadow-md transition-all">
                    <i class="bi bi-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="font-bold text-gray-900 text-lg leading-tight">Scan to Pay</h1>
                    <p class="text-gray-500 text-[11px]">Use any Bakong-supported banking app</p>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-white px-3.5 py-2 rounded-full border border-gray-200 shadow-sm text-[11px] font-semibold text-gray-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Ref: <strong class="text-gray-900">{{ $booking->referenceNumber() }}</strong></span>
            </div>
        </div>

        {{-- ── Two-Column Grid ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">

            {{-- ══ LEFT: Official KHQR Card ══ --}}
            <div class="lg:col-span-5 flex flex-col items-center">

                <div class="khqr-card">

                    {{-- Red Header --}}
                    <div class="khqr-header">
                        <div class="flex items-center justify-between mb-1">
                            {{-- Official KHQR Logo (white) --}}
                            <img src="/images/khqr-logo-white.svg"
                                 alt="KHQR"
                                 class="khqr-logo-img">
                            <span class="khqr-tag-badge">Tag 29</span>
                        </div>
                    </div>

                    {{-- Amount Due & Room --}}
                    <div class="px-6 pt-5 pb-1">
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-[10px] font-bold tracking-widest text-gray-400 uppercase mb-0.5">Amount Due</p>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-[32px] font-black text-gray-900 leading-none tracking-tight">
                                        {{ number_format($transaction->amount_paid, 2) }}
                                    </span>
                                    <span class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wider bg-gray-100 px-1.5 py-0.5 rounded-md">
                                        USD
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold tracking-widest text-gray-400 uppercase mb-0.5">Room</p>
                                <p class="text-sm font-extrabold text-gray-800">{{ $booking->room?->displayType() ?? 'Reserved' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Ticket Divider --}}
                    <div class="ticket-sep px-6 my-4">
                        <div class="ticket-sep-line"></div>
                    </div>

                    {{-- QR Code --}}
                    <div class="px-6 pb-6 flex flex-col items-center">
                        <div class="qr-frame">
                            <div id="khqr-canvas" class="w-[220px] h-[220px] flex items-center justify-center">
                                <div class="animate-pulse text-gray-400 text-xs">Generating…</div>
                            </div>

                            {{-- Bakong Centre Emblem — official temple logo --}}
                            <div class="qr-emblem">
                                <img src="/images/bakong-emblem.svg"
                                     alt="Bakong"
                                     class="w-8 h-8 object-contain">
                            </div>
                        </div>

                        {{-- Footer badge --}}
                        <div class="flex items-center gap-1.5 mt-4 text-[11px] text-gray-500 font-medium">
                            <svg class="w-3 h-3 text-[#D0021B]" viewBox="0 0 12 12" fill="currentColor">
                                <circle cx="6" cy="6" r="6"/>
                            </svg>
                            Powered by <strong class="text-gray-700 ml-0.5">Bakong — NBC</strong>
                        </div>
                    </div>

                </div>

                {{-- ── Status + Timer strip ── --}}
                <div class="w-full max-w-[360px] mt-4 space-y-3">

                    <div id="payment-status" class="status-pill">
                        <div class="flex items-center gap-2.5">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                            </span>
                            Waiting for payment…
                        </div>
                        <div class="flex items-center gap-1.5 font-semibold text-gray-500">
                            <i class="bi bi-clock text-xs"></i>
                            <span id="countdown">15:00</span>
                        </div>
                    </div>

                    @if(! app()->isProduction())
                    <form method="POST" action="{{ route('payment.simulate', $booking) }}"
                          onsubmit="return confirm('DEMO: Simulate a successful Bakong payment?')">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 text-emerald-700 font-bold rounded-xl py-2.5 text-xs transition-all">
                            <i class="bi bi-check-circle-fill"></i> Simulate Successful Payment (Demo)
                        </button>
                    </form>
                    @endif

                </div>

            </div>

            {{-- ══ RIGHT: Info Panels ══ --}}
            <div class="lg:col-span-7 space-y-5">

                {{-- Supported Apps --}}
                <div class="info-card">
                    <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider mb-1 flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-[#D0021B] flex items-center justify-center">
                            <i class="bi bi-phone-fill text-white" style="font-size:8px;"></i>
                        </span>
                        Supported Banking Apps
                    </h3>
                    <p class="text-[11px] text-gray-500 mb-4 leading-relaxed">
                        Open any app supporting <strong class="text-gray-700">KHQR / Bakong</strong> — scan and pay with zero fees.
                    </p>
                    <div class="grid grid-cols-4 gap-3">
                        <div class="app-icon">
                            <div class="app-icon-badge overflow-hidden p-0">
                                <img src="/images/aba-logo.webp" alt="ABA Mobile" class="w-full h-full object-cover rounded-xl">
                            </div>
                            ABA Mobile
                        </div>
                        <div class="app-icon">
                            <div class="app-icon-badge overflow-hidden p-0">
                                <img src="/images/wing-logo.jpg" alt="Wing Bank" class="w-full h-full object-cover rounded-xl">
                            </div>
                            Wing Bank
                        </div>
                        <div class="app-icon">
                            <div class="app-icon-badge overflow-hidden p-0">
                                <img src="/images/acleda-logo.jpg" alt="ACLEDA" class="w-full h-full object-cover rounded-xl">
                            </div>
                            ACLEDA
                        </div>
                        <div class="app-icon">
                            <div class="app-icon-badge overflow-hidden p-0">
                                <img src="/images/bakong-app-logo.png" alt="Bakong" class="w-full h-full object-cover rounded-xl">
                            </div>
                            Bakong
                        </div>
                    </div>
                </div>

                {{-- Steps --}}
                <div class="info-card">
                    <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full bg-[#D0021B] flex items-center justify-center">
                            <i class="bi bi-list-ol text-white" style="font-size:8px;"></i>
                        </span>
                        How to Pay
                    </h3>
                    <div class="space-y-3.5">
                        @foreach([
                            ['Open your banking app', 'ABA Mobile, Wing Bank, ACLEDA or any Bakong-connected app'],
                            ['Tap KHQR / Scan QR', 'Point your phone camera at the QR code on the left'],
                            ['Verify the details', 'Check the amount is <strong class="text-gray-800">$'.number_format($transaction->amount_paid,2).' USD</strong>'],
                            ['Confirm payment', 'This page updates <strong class="text-gray-800">automatically</strong> — no refresh needed'],
                        ] as $i => [$title, $desc])
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-extrabold shrink-0 mt-0.5"
                                 style="background:rgba(208,2,27,0.08); color:#D0021B;">{{ $i+1 }}</div>
                            <div>
                                <p class="text-[12px] font-bold text-gray-800 mb-0">{{ $title }}</p>
                                <p class="text-[11px] text-gray-500 leading-relaxed mt-0.5">{!! $desc !!}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Booking Summary --}}
                <div class="info-card">
                    <h3 class="text-xs font-extrabold text-gray-900 uppercase tracking-wider mb-4">
                        Booking Summary
                    </h3>
                    <div class="space-y-0 divide-y divide-gray-100 text-[12px]">
                        @if($booking->guest_name)
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-400 font-medium">Guest</span>
                            <span class="font-bold text-gray-800">{{ $booking->guest_name }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-400 font-medium">Room</span>
                            <span class="font-bold text-gray-800">{{ $booking->room?->displayType() ?? 'Room' }}</span>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-400 font-medium">Check-in</span>
                            <span class="font-bold text-gray-800">{{ $booking->check_in_date?->format('D, d M Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2.5">
                            <span class="text-gray-400 font-medium">Check-out</span>
                            <span class="font-bold text-gray-800">{{ $booking->check_out_date?->format('D, d M Y') }}</span>
                        </div>
                        <div class="flex justify-between py-3 mt-1">
                            <span class="font-extrabold text-gray-800 text-[13px]">Total Payable</span>
                            <span class="font-extrabold text-[13px]" style="color:#D0021B;">${{ number_format($transaction->amount_paid, 2) }} USD</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
        integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(function () {
    const khqrString = @json($khqrData['khqr_string']);
    const checkUrl   = @json(route('payment.check-status', $booking->id));
    const successUrl = @json(route('payment.success', $booking->id));

    // ── Render QR (High error-correction so center emblem doesn't break scan) ──
    const container = document.getElementById('khqr-canvas');
    container.innerHTML = '';
    new QRCode(container, {
        text:         khqrString,
        width:        220,
        height:       220,
        colorDark:    '#0a0a0a',
        colorLight:   '#ffffff',
        correctLevel: QRCode.CorrectLevel.H,
    });

    // ── 15-min Countdown ─────────────────────────────────────────────────
    let secs = 15 * 60;
    const countEl = document.getElementById('countdown');
    const timer = setInterval(() => {
        secs--;
        const m = String(Math.floor(secs / 60)).padStart(2, '0');
        const s = String(secs % 60).padStart(2, '0');
        countEl.textContent = `${m}:${s}`;
        if (secs <= 0) {
            clearInterval(timer);
            clearInterval(poll);
            countEl.textContent = 'Expired';
            countEl.style.color = '#ef4444';
        }
    }, 1000);

    // ── Bakong Payment Polling (every 3.5 sec) ────────────────────────────
    const statusEl = document.getElementById('payment-status');
    const poll = setInterval(async () => {
        try {
            const r = await fetch(checkUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const d = await r.json();
            if (d.paid) {
                clearInterval(poll);
                clearInterval(timer);
                statusEl.classList.add('paid');
                statusEl.innerHTML = `
                    <div class="flex items-center gap-2">
                        <i class="bi bi-check-circle-fill text-emerald-600"></i>
                        Payment confirmed! Redirecting…
                    </div>
                    <i class="bi bi-arrow-repeat animate-spin text-emerald-600"></i>
                `;
                setTimeout(() => { window.location.href = d.redirect || successUrl; }, 1200);
            }
        } catch (_) {}
    }, 3500);
})();
</script>
@endpush
