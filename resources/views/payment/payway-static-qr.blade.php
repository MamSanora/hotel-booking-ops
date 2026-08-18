@extends('layouts.public')

@section('title', 'Payment — ' . $booking->referenceNumber())

@section('content')

{{-- 
    AGENT NOTE: 
    Development is no longer needed for this offline QR code generator page. 
    We have successfully acquired and integrated the official Bakong Payment API.
    (This view is kept for legacy/fallback purposes only). 
--}}

<div class="min-h-[85vh] bg-[#f0f2f5] py-10 px-4">
    <div class="max-w-5xl w-full mx-auto">

        {{-- Top Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-7">
            <div class="flex items-center gap-3">
                <a href="{{ route('guest.booking.show', $booking) }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-gray-800 transition-colors text-sm">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 leading-snug">Complete Payment &mdash; KHQR / ABA Pay</h1>
                    <p class="text-xs text-gray-500">Scan the QR code below to transfer the exact amount</p>
                </div>
            </div>
            <div class="inline-flex items-center gap-2 text-sm font-semibold text-gray-700">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                Ref:&nbsp;<strong class="text-gray-900">{{ $booking->referenceNumber() }}</strong>
            </div>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- LEFT: QR Card --}}
            <div class="lg:col-span-5 flex flex-col items-center">

                {{-- KHQR Card using the template image. The QR matrix is generated
                     dynamically by qrcodejs and overlaid in the exact same position. --}}
                <div class="w-full max-w-[340px] rounded-2xl overflow-hidden shadow-[0_8px_30px_rgba(0,0,0,0.12)]">
                    <div class="relative" style="width: 106%; margin-left: -3%; margin-top: -3%; margin-bottom: -3%;">
                        {{-- Background Template Image --}}
                        <img src="{{ asset('qr_codes/hotel_owner_QR_codes/KHQR_Code_Template.jpg') }}" alt="KHQR Template" class="w-full h-auto">

                        {{-- White box to cover the existing "0" in the image --}}
                        <div class="absolute bg-white flex items-center" style="top: 24%; left: 10%; right: 10%; height: 10%; padding-left: 2%;">
                            <span class="text-3xl font-bold text-gray-900 mr-2">$</span>
                            <span class="text-4xl font-bold text-gray-900 tracking-tight">{{ number_format($transaction->amount_paid, 2) }}</span>
                        </div>

                        {{-- Dynamic QR Code Matrix Overlay (rendered by qrcodejs) --}}
                        {{-- We wrap this in a larger white block to completely occlude the static QR code on the template --}}
                        <div class="absolute flex items-center justify-center bg-white" style="top: 37%; left: 11%; width: 78%; height: auto; aspect-ratio: 1/1; padding: 2%;">
                            <div id="khqr-matrix" class="w-full h-full flex items-center justify-center"></div>
                        </div>
                    </div>
                </div>

                {{-- qrcodejs: renders the KHQR string as a QR matrix locally in the browser --}}
                <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new QRCode(document.getElementById('khqr-matrix'), {
                            text: @json($khqrString),
                            width: document.getElementById('khqr-matrix').offsetWidth || 256,
                            height: document.getElementById('khqr-matrix').offsetWidth || 256,
                            colorDark:  '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    });
                </script>

                {{-- Awaiting confirmation badge --}}
                <div class="mt-4 w-full max-w-[300px] bg-white border border-gray-200/80 rounded-xl py-3 px-4 shadow-sm flex items-center justify-between text-xs font-medium text-gray-600">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        Awaiting payment confirmation&hellip;
                    </div>
                    <div class="flex items-center gap-1 text-gray-500 font-semibold">
                        <i class="bi bi-clock text-[11px]"></i>
                        <span id="countdown">01:00</span>
                    </div>
                </div>

                {{-- Simulate Button (Hidden) --}}
                @if(! app()->isProduction())
                <div class="mt-2 w-full max-w-[300px] hidden">
                    <form method="POST" action="{{ route('payment.simulate', $booking) }}"
                          x-data @submit.prevent="$dispatch('open-confirm', { message: 'DEMO MODE: Simulate a successful payment?', action: (function(f) { return () => f.submit(); })($el) })">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold border border-emerald-300 rounded-xl py-2.5 text-xs transition-all shadow-sm">
                            <i class="bi bi-check-circle-fill"></i> Simulate Successful Payment (Local Demo)
                        </button>
                    </form>
                </div>
                @endif

            </div>

            {{-- RIGHT: Booking Summary + Security Note --}}
            <div class="lg:col-span-7 space-y-4">

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
                            <span class="font-semibold text-gray-900">{{ $booking->displayRooms() }}</span>
                        </div>
                        <div class="py-2.5 flex justify-between items-center">
                            <span class="text-gray-500">Check-in</span>
                            <span class="font-semibold text-gray-900">{{ $booking->check_in_date?->format('D, M d, Y') }} (2:00 PM)</span>
                        </div>
                        @if($transaction->payment_for === \App\Models\Transaction::FOR_STAY_EXTENSION)
                            <div class="py-2.5 flex justify-between items-center">
                                <span class="text-gray-500">New Check-out</span>
                                <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($transaction->extension_new_checkout)->format('D, M d, Y') }} (12:00 PM)</span>
                            </div>
                            <div class="py-2.5 flex justify-between items-center">
                                <span class="text-gray-500">Extension</span>
                                <span class="font-semibold text-gray-900">{{ $transaction->extension_nights }} Night(s)</span>
                            </div>
                            <div class="pt-3 pb-1 flex justify-between items-center border-t border-gray-100">
                                <span class="text-sm font-bold text-gray-800">Extension Cost</span>
                                <span class="text-sm font-bold text-[#D62B2B]">${{ number_format($transaction->amount_paid, 2) }} USD</span>
                            </div>
                        @else
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
                        @endif
                    </div>
                </div>

                {{-- Security note --}}
                <div class="bg-[#fff5f5] border border-[#fecdcc] rounded-2xl p-4 flex items-start gap-3">
                    <i class="bi bi-shield-lock-fill text-[#D62B2B] text-xl shrink-0 mt-0.5"></i>
                    <div class="text-[0.8rem] text-[#6b1010]">
                        <p class="font-bold mb-0.5">Processed via KHQR &mdash; National Bank of Cambodia</p>
                        <p class="text-[#8b2020]">KHQR is the official interoperable QR payment standard regulated by the National Bank of Cambodia. Your banking credentials are never shared with Dara Meas Hotel's servers.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Poll the server every 3 seconds to see if Telegram webhook marked it paid
        const pollInterval = setInterval(() => {
            fetch("{{ route('payment.check-status', $booking->id) }}")
                .then(response => response.json())
                .then(data => {
                    if (data.paid && data.redirect) {
                        clearInterval(pollInterval);
                        if (window.setRedirecting) window.setRedirecting();
                        window.location.href = data.redirect;
                    }
                })
                .catch(err => console.error('Polling error:', err));
        }, 3000);
    });
</script>

@include('payment.partials.countdown-script')
@include('payment.partials.unlock-script')
@endsection
