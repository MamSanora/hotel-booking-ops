@extends('layouts.public')

@section('title', 'Complete Payment — ' . $booking->referenceNumber())

@section('content')

<div class="min-h-[85vh] bg-[#f4f6f9] py-12 px-4 flex items-center justify-center">
    <div class="max-w-5xl w-full mx-auto">

        {{-- Top Navigation & Reference Bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-3">
                <a href="{{ route('guest.booking.show', $booking) }}"
                   class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm border border-gray-200 text-gray-600 hover:text-hotel-dark transition-colors">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 leading-tight">Complete KHQR Payment</h1>
                    <p class="text-xs text-gray-500">Scan & pay instantly with any Cambodian banking app</p>
                </div>
            </div>

            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-gray-200 shadow-sm text-xs font-semibold text-gray-700">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Ref: <strong class="text-gray-900">{{ $booking->referenceNumber() }}</strong>
            </div>
        </div>

        {{-- Main Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- ── Left: Official KHQR Card ── --}}
            <div class="lg:col-span-6 flex flex-col items-center">

                <div class="w-full max-w-[370px] bg-white rounded-[24px] shadow-[0_16px_40px_rgba(0,0,0,0.08)] border border-gray-200/80 overflow-hidden">

                    {{-- Red Header (Bakong signature slanted corner) --}}
                    <div class="relative bg-[#E1232C] pt-6 pb-6 px-7 text-white"
                         style="clip-path: polygon(0 0, 100% 0, 100% 58%, 87% 100%, 0 100%);">
                        <div class="flex items-center justify-between">
                            {{-- Official KHQR Logo (white) --}}
                            <img src="/images/khqr-logo-white.svg"
                                 alt="KHQR"
                                 style="height:28px;width:auto;display:block;filter:brightness(0) invert(1);">
                            <span class="text-[10px] font-bold tracking-widest uppercase bg-white/20 px-2.5 py-1 rounded">Tag 29</span>
                        </div>
                    </div>

                    {{-- Amount Due & Room --}}
                    <div class="px-7 pt-5 pb-2">
                        <div class="flex items-baseline justify-between">
                            <div>
                                <div class="text-[11px] font-bold tracking-wider text-gray-400 uppercase">Amount Due</div>
                                <div class="flex items-baseline gap-1.5 mt-0.5">
                                    <span class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($booking->total_price, 2) }}</span>
                                    <span class="text-xs font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 uppercase">{{ $paymentData['currency'] ?? 'USD' }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-[11px] font-bold tracking-wider text-gray-400 uppercase">Room</div>
                                <div class="text-sm font-bold text-gray-800 mt-0.5">#{{ $booking->room?->room_number ?? $booking->room_id }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Ticket Dashed Divider --}}
                    <div class="relative my-4 flex items-center">
                        <div class="absolute -left-3 w-6 h-6 rounded-full bg-[#f4f6f9] border border-gray-200/80 z-10"></div>
                        <div class="w-full border-b-2 border-dashed border-gray-200 mx-4"></div>
                        <div class="absolute -right-3 w-6 h-6 rounded-full bg-[#f4f6f9] border border-gray-200/80 z-10"></div>
                    </div>

                    {{-- QR Code + Bakong Emblem --}}
                    <div class="px-7 pb-8 pt-2 flex flex-col items-center">
                        <div class="relative bg-white p-3.5 rounded-2xl border-2 border-gray-100 shadow-[0_4px_16px_rgba(0,0,0,0.05)]">

                            <div class="w-[230px] h-[230px] flex items-center justify-center">
                                @if(!empty($paymentData['qr_image']))
                                    <img src="{{ $paymentData['qr_image'] }}" alt="ABA PayWay QR Code" class="w-full h-full object-contain">
                                @else
                                    <div id="khqr-fallback" class="animate-pulse text-gray-400 text-xs font-medium text-center px-4">
                                        <i class="bi bi-qr-code text-5xl text-gray-300 block mb-2"></i>
                                        QR code unavailable.<br>Please use the deeplink below.
                                    </div>
                                @endif
                            </div>

                            {{-- Center Bakong Emblem — official temple logo --}}
                            <div class="absolute inset-0 m-auto w-12 h-12 bg-white rounded-full shadow-[0_2px_10px_rgba(0,0,0,0.18)] border-[2.5px] border-white flex items-center justify-center pointer-events-none z-10">
                                <img src="/images/bakong-emblem.svg"
                                     alt="Bakong"
                                     class="w-8 h-8 object-contain">
                            </div>
                        </div>

                        {{-- ABA deeplink (mobile only) --}}
                        @if(!empty($paymentData['abapay_deeplink']))
                        <a href="{{ $paymentData['abapay_deeplink'] }}"
                           class="mt-4 w-full flex items-center justify-center gap-2 bg-[#004B87] hover:bg-[#003a6a] text-white font-bold rounded-xl px-6 py-2.5 transition-all text-xs md:hidden">
                            <i class="bi bi-phone"></i> Open in ABA Mobile
                        </a>
                        @endif

                        <div class="mt-5 flex items-center justify-center gap-2 text-gray-500 text-[11px] font-medium">
                            <span class="w-2 h-2 rounded-full bg-[#E1232C]"></span>
                            <span>Supported by <strong class="text-gray-800">Bakong &bull; NBC</strong></span>
                        </div>
                    </div>

                </div>

                {{-- Status & Timer Row --}}
                <div class="mt-5 flex flex-col items-center gap-2.5 w-full max-w-[370px]">

                    <div id="payment-status" class="w-full bg-white border border-gray-200/80 rounded-xl py-3 px-4 shadow-sm flex items-center justify-between text-xs font-medium text-gray-600">
                        <div class="flex items-center gap-2.5">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                            </span>
                            Waiting for payment…
                        </div>
                        <div class="flex items-center gap-1.5 text-gray-500 font-semibold">
                            <i class="bi bi-clock"></i>
                            <span id="countdown">15:00</span>
                        </div>
                    </div>

                    @if(! app()->isProduction())
                    <div class="w-full pt-2">
                        <form method="POST" action="{{ route('payment.simulate', $booking) }}"
                              onsubmit="return confirm('DEMO MODE: Simulate a successful payment?')">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold border border-emerald-300 rounded-xl py-2.5 text-xs transition-all shadow-sm">
                                <i class="bi bi-check-circle-fill text-sm"></i> Simulate Successful Payment (Local Demo)
                            </button>
                        </form>
                    </div>
                    @endif

                </div>
            </div>

            {{-- ── Right: Info Panels ── --}}
            <div class="lg:col-span-6 space-y-6">

                {{-- Supported Apps --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="bi bi-phone text-[#E1232C] text-base"></i> Supported Banking Apps
                    </h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-5">
                        Open any banking app in Cambodia that supports <strong>KHQR / Bakong</strong> to scan and pay instantly with zero transfer fees.
                    </p>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        <div class="flex flex-col items-center p-3 rounded-xl bg-gray-50 border border-gray-100 text-center">
                            <div class="w-9 h-9 rounded-lg overflow-hidden mb-1.5 shadow-sm">
                                <img src="/images/aba-logo.webp" alt="ABA Mobile" class="w-full h-full object-cover">
                            </div>
                            <span class="text-[11px] font-bold text-gray-700">ABA Mobile</span>
                        </div>
                        <div class="flex flex-col items-center p-3 rounded-xl bg-gray-50 border border-gray-100 text-center">
                            <div class="w-9 h-9 rounded-lg overflow-hidden mb-1.5 shadow-sm">
                                <img src="/images/wing-logo.jpg" alt="Wing Bank" class="w-full h-full object-cover">
                            </div>
                            <span class="text-[11px] font-bold text-gray-700">Wing Bank</span>
                        </div>
                        <div class="flex flex-col items-center p-3 rounded-xl bg-gray-50 border border-gray-100 text-center">
                            <div class="w-9 h-9 rounded-lg overflow-hidden mb-1.5 shadow-sm">
                                <img src="/images/acleda-logo.jpg" alt="ACLEDA" class="w-full h-full object-cover">
                            </div>
                            <span class="text-[11px] font-bold text-gray-700">ACLEDA</span>
                        </div>
                        <div class="flex flex-col items-center p-3 rounded-xl bg-gray-50 border border-gray-100 text-center">
                            <div class="w-9 h-9 rounded-lg overflow-hidden mb-1.5 shadow-sm">
                                <img src="/images/bakong-app-logo.png" alt="Bakong" class="w-full h-full object-cover">
                            </div>
                            <span class="text-[11px] font-bold text-gray-700">Bakong</span>
                        </div>
                    </div>
                </div>

                {{-- Steps --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <i class="bi bi-shield-check text-[#E1232C] text-base"></i> Quick Payment Steps
                    </h3>
                    <div class="space-y-4">
                        @foreach([
                            'Open <strong class="text-gray-900">ABA Mobile, Wing Bank, ACLEDA</strong> or your preferred banking app.',
                            'Tap <strong class="text-gray-900">KHQR / Scan QR</strong> and point your camera at the code.',
                            'Verify the amount is <strong class="text-gray-900">$'.(number_format($booking->total_price,2)).' USD</strong>.',
                            'Once paid, this page will <strong class="text-gray-900">automatically redirect</strong> to your receipt.',
                        ] as $i => $step)
                        <div class="flex items-start gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-[#E1232C]/10 text-[#E1232C] font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">{{ $i + 1 }}</div>
                            <div class="text-xs text-gray-600 leading-relaxed">{!! $step !!}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Booking Summary --}}
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Booking Summary</h3>
                    <div class="divide-y divide-gray-100 text-xs">
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Guest Name</span>
                            <span class="font-bold text-gray-900">{{ $booking->guest_name }}</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Room Type</span>
                            <span class="font-bold text-gray-900">{{ $booking->room?->displayType() ?? 'Room #'.$booking->room_id }}</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Check-in</span>
                            <span class="font-bold text-gray-900">{{ $booking->check_in_date?->format('D, M d, Y') }} (2:00 PM)</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Check-out</span>
                            <span class="font-bold text-gray-900">{{ $booking->check_out_date?->format('D, M d, Y') }} (12:00 PM)</span>
                        </div>
                        <div class="py-3 flex justify-between items-center text-sm font-bold border-t border-gray-200 mt-1">
                            <span class="text-gray-700">Total Payable</span>
                            <span class="text-[#E1232C]">${{ number_format($booking->total_price, 2) }} USD</span>
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
})();
</script>
@endpush
