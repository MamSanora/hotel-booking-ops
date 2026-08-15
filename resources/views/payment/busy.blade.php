@extends('layouts.public')

@section('title', 'Payment System Busy')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-gradient-to-br from-amber-50 to-orange-50 px-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-10 text-center border border-amber-100">

        {{-- Icon --}}
        <div class="w-20 h-20 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-6">
            <i class="bi bi-hourglass-split text-4xl text-amber-500 animate-pulse"></i>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment in Progress</h1>
        <p class="text-gray-500 text-sm mb-6">
            Another guest is currently completing their payment.<br>
            Our system processes one payment at a time to ensure accuracy.
        </p>

        {{-- Countdown --}}
        @php
            $secondsLeft = max(0, now()->diffInSeconds($expiresAt, false));
        @endphp
        <div class="bg-amber-50 border border-amber-200 rounded-2xl px-6 py-4 mb-6">
            <p class="text-xs uppercase tracking-widest text-amber-600 font-bold mb-1">Retrying in</p>
            <p id="countdown-display" class="text-4xl font-mono font-extrabold text-amber-700">
                <span id="cd-secs">{{ max(5, $secondsLeft) }}</span>s
            </p>
            <p class="text-xs text-amber-500 mt-1">This page will automatically redirect you.</p>
        </div>

        {{-- Manual retry --}}
        <button onclick="window.location.reload()" class="inline-flex items-center gap-2 bg-hotel-gold hover:bg-[#b8935a] text-white font-bold px-6 py-2.5 rounded-xl transition-colors shadow text-sm">
            <i class="bi bi-arrow-clockwise"></i> Try Now
        </button>

        <p class="text-xs text-gray-400 mt-6">
            If this persists for more than 15 minutes, please contact the front desk.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    let secs = {{ max(5, $secondsLeft) }};
    const display = document.getElementById('cd-secs');

    const tick = setInterval(() => {
        secs--;
        if (display) display.textContent = secs;

        if (secs <= 0) {
            clearInterval(tick);
            window.location.reload();
        }
    }, 1000);
})();
</script>
@endpush
