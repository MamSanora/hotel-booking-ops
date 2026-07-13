<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code — Dara Meas Hotel</title>
    <meta name="description" content="Enter the recovery code sent to you.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="min-h-screen bg-gray-50 font-[Inter] flex items-center justify-center p-6">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <div class="flex items-center justify-center mb-8">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Dara Meas Hotel" class="h-10 w-auto object-contain">
        </a>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-[0_4px_30px_rgba(0,0,0,0.08)] border border-gray-100 p-8 md:p-10">

        {{-- Icon + Title --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-hotel-gold/10 flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-shield-lock text-3xl text-hotel-gold"></i>
            </div>
            <h1 class="font-[Playfair_Display] text-2xl font-bold text-hotel-dark mb-2">Enter Recovery Code</h1>
            <p class="text-sm text-gray-500 leading-relaxed">
                We've sent a 6-digit recovery code to your account's email or phone number.
            </p>
        </div>

        {{-- DEV MODE notice --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-6">
            <p class="text-xs text-amber-700 font-semibold mb-1">
                <i class="bi bi-wrench-adjustable-circle mr-1"></i> Developer / Demo Mode
            </p>
            <p class="text-xs text-amber-600">
                Check <code class="bg-amber-100 rounded px-1 py-0.5 font-mono text-[0.7rem]">storage/logs/laravel.log</code> for the OTP code.
            </p>
        </div>

        {{-- Info flash --}}
        @if(session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 text-sm mb-5 flex items-center gap-2">
                <i class="bi bi-info-circle-fill text-blue-500"></i> {{ session('info') }}
            </div>
        @endif

        {{-- OTP Form --}}
        <form method="POST" action="{{ route('guest.forgot-password.verify.submit') }}" id="otp-form" class="space-y-6">
            @csrf

            {{-- 6 individual digit boxes --}}
            <div>
                <label class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-3 text-center">
                    Enter your 6-digit code
                </label>

                {{-- Hidden input that holds the concatenated value --}}
                <input type="hidden" name="otp" id="otp-hidden" value="{{ old('otp') }}">

                <div class="flex gap-2 justify-center" id="otp-boxes">
                    @for ($i = 0; $i < 6; $i++)
                        <input type="text"
                               maxlength="1"
                               inputmode="numeric"
                               pattern="[0-9]"
                               data-index="{{ $i }}"
                               class="otp-box w-12 h-14 text-center text-xl font-bold border-2 border-gray-200 rounded-xl focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('otp') border-red-400 @enderror"
                               autocomplete="off">
                    @endfor
                </div>

                @error('otp')
                    <p class="text-red-500 text-xs mt-3 text-center flex items-center justify-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-hotel-dark hover:bg-black text-white font-semibold rounded-xl py-3.5 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
                <i class="bi bi-check-circle"></i> Verify Code
            </button>
        </form>

        {{-- Back --}}
        <div class="mt-6 text-center">
            <a href="{{ route('guest.login') }}" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                <i class="bi bi-arrow-left mr-1"></i> Back to sign in
            </a>
        </div>

    </div>
</div>

<script>
(function () {
    const boxes      = document.querySelectorAll('.otp-box');
    const hidden     = document.getElementById('otp-hidden');
    const form       = document.getElementById('otp-form');

    function sync() {
        hidden.value = Array.from(boxes).map(b => b.value).join('');
    }

    boxes.forEach((box, i) => {
        // Prepopulate from old('otp') if validation failed.
        const oldVal = hidden.value;
        if (oldVal && oldVal[i]) box.value = oldVal[i];

        box.addEventListener('input', function (e) {
            // Allow only digits.
            this.value = this.value.replace(/\D/g, '').slice(-1);
            sync();
            if (this.value && i < boxes.length - 1) boxes[i + 1].focus();
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !this.value && i > 0) {
                boxes[i - 1].focus();
            }
        });

        // Handle paste: distribute across boxes.
        box.addEventListener('paste', function (e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            text.split('').slice(0, 6).forEach((ch, j) => {
                if (boxes[j]) boxes[j].value = ch;
            });
            sync();
            const next = Math.min(text.length, 5);
            boxes[next].focus();
        });
    });

    // Auto-submit when all 6 digits are filled.
    function checkAutoSubmit() {
        if (hidden.value.length === 6) form.submit();
    }

    boxes.forEach(b => b.addEventListener('input', checkAutoSubmit));

    // Focus first empty box on load.
    const firstEmpty = Array.from(boxes).find(b => !b.value);
    if (firstEmpty) firstEmpty.focus();
})();
</script>

</body>
</html>
