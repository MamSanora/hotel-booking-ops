<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Dara Meas Hotel</title>
    <meta name="description" content="Sign in to your Dara Meas Hotel guest account to manage your bookings.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="min-h-screen bg-gray-50 font-[Inter]">

<div class="min-h-screen flex">

    {{-- ── Left Panel: Branding ──────────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-hotel-dark via-hotel-accent to-[#0f3460] relative overflow-hidden flex-col justify-between p-12">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=60')] bg-cover bg-center opacity-10"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-hotel-dark/90 to-transparent"></div>

        {{-- Logo --}}
        <div class="relative z-10">
            <a href="{{ url('/') }}" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Dara Meas Hotel" class="h-12 w-auto object-contain brightness-0 invert">
            </a>
        </div>

        {{-- Quote --}}
        <div class="relative z-10">
            <blockquote class="font-[Playfair_Display] text-white/90 text-3xl font-semibold leading-snug mb-6">
                "Your home away<br>from home in the<br>heart of Cambodia."
            </blockquote>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full border-2 border-hotel-gold/40 bg-hotel-gold/10 flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-building text-hotel-gold text-2xl"></i>
                </div>
                <div>
                    <div class="text-white font-medium text-sm">Dara Meas Hotel</div>
                    <div class="text-hotel-gold text-xs">Est. 2019 · 2 Stars · 47 Rooms</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Right Panel: Login Form ─────────────────────────────────────── --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-md">

            {{-- Mobile Logo --}}
            <div class="lg:hidden flex items-center mb-8">
                <img src="{{ asset('images/logo.png') }}" alt="Dara Meas Hotel" class="h-10 w-auto object-contain">
            </div>

            <h1 class="font-[Playfair_Display] text-3xl font-bold text-hotel-dark mb-1">Welcome back</h1>
            <p class="text-gray-500 text-sm mb-8">Sign in with your email address or phone number.</p>

            {{-- Session Error --}}
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-6 flex items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-red-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('guest.login.post') }}" class="space-y-5">
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
                               required autofocus autocomplete="username"
                               placeholder="e.g. your@email.com or +855 12 345 678"
                               class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('identifier') border-red-400 @enderror">
                    </div>
                    @error('identifier')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider">
                            Password
                        </label>
                        <a href="{{ route('guest.forgot-password') }}" class="text-[0.8rem] font-semibold text-hotel-gold hover:text-hotel-dark transition-colors">
                            Forgot Password?
                        </a>
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center gap-2">
                    <input id="remember" type="checkbox" name="remember"
                           class="w-4 h-4 rounded border-gray-300 text-hotel-gold accent-[#c8a96e]">
                    <label for="remember" class="text-sm text-gray-600">Remember me</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-hotel-dark hover:bg-black text-white font-semibold rounded-xl py-3.5 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            {{-- Register Link --}}
            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account?
                <a href="{{ route('guest.register') }}{{ request('redirect') ? '?redirect='.urlencode(request('redirect')) : '' }}" class="text-hotel-dark font-semibold hover:text-hotel-gold transition-colors">
                    Create one
                </a>
            </p>

            {{-- Staff Login Links --}}
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400 mb-3 uppercase tracking-wider">Staff Access</p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('admin.login') }}"
                       class="text-xs text-gray-500 hover:text-hotel-dark transition-colors flex items-center gap-1">
                        <i class="bi bi-shield-lock"></i> Admin Portal
                    </a>
                    <span class="text-gray-200">|</span>
                    <a href="{{ route('reception.login') }}"
                       class="text-xs text-gray-500 hover:text-hotel-dark transition-colors flex items-center gap-1">
                        <i class="bi bi-person-badge"></i> Reception Portal
                    </a>
                </div>
            </div>

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
