<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — Dara Meas Hotel</title>
    <meta name="description" content="Recover your Dara Meas Hotel account password.">
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

        {{-- Title --}}
        <div class="text-center mb-8">
            <h1 class="font-[Playfair_Display] text-2xl font-bold text-hotel-dark mb-2">Forgot Password</h1>
            <p class="text-sm text-gray-500 leading-relaxed">
                Enter your email address or phone number, and we'll send you a link to reset your password.
            </p>
        </div>

        {{-- Session Error --}}
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-6 flex items-center gap-2">
                <i class="bi bi-exclamation-circle-fill text-red-500"></i>
                {{ session('error') }}
            </div>
        @endif
        
        {{-- Session Success/Info --}}
        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-4 py-3 text-sm mb-6 flex items-center gap-2">
                <i class="bi bi-info-circle-fill text-blue-500"></i>
                {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="{{ route('guest.forgot-password.send') }}" class="space-y-6">
            @csrf

            {{-- Identifier (email or phone) --}}
            <div>
                <label for="identifier" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                    Email or Phone Number
                </label>
                <div class="relative">
                    <span id="identifier-icon" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-base transition-all">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input id="identifier" type="text" name="identifier"
                           value="{{ old('identifier') }}"
                           required autofocus
                           placeholder="e.g. your@email.com or +855 12 345 678"
                           class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('identifier') border-red-400 @enderror">
                </div>
                @error('identifier')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-hotel-dark hover:bg-black text-white font-semibold rounded-xl py-3.5 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
                <i class="bi bi-send"></i> Send Recovery Code
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
    // Swap the icon in the identifier field based on input type.
    (function () {
        const input = document.getElementById('identifier');
        const icon  = document.getElementById('identifier-icon');
        if (!input || !icon) return;

        function update() {
            const val = input.value.trim();
            const isPhone = val !== '' && !val.includes('@') && /^[\d\+\s\-\(\)]+$/.test(val);
            icon.innerHTML = isPhone
                ? '<i class="bi bi-telephone"></i>'
                : '<i class="bi bi-envelope"></i>';
        }

        input.addEventListener('input', update);
        update();
    })();
</script>

</body>
</html>
