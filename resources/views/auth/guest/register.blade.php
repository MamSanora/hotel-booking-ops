<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Dara Meas Hotel</title>
    <meta name="description" content="Register a new guest account at Dara Meas Hotel to book rooms online.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="min-h-screen bg-gray-50 font-[Inter]">

<div class="min-h-screen flex">

    {{-- ── Left Panel: Branding ──────────────────────────────────────────── --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-hotel-dark via-hotel-accent to-[#0f3460] relative overflow-hidden flex-col justify-between p-12">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1200&q=60')] bg-cover bg-center opacity-10"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-hotel-dark/90 to-transparent"></div>

        {{-- Logo --}}
        <div class="relative z-10">
            <a href="{{ url('/') }}" class="flex items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Dara Meas Hotel" class="h-12 w-auto object-contain brightness-0 invert">
            </a>
        </div>

        {{-- Perks --}}
        <div class="relative z-10 space-y-4">
            <h2 class="font-[Playfair_Display] text-white text-2xl font-bold mb-6">Join our community</h2>
            @foreach([
                ['bi-calendar-check', 'Easy Online Booking', 'Book any room in minutes, anytime.'],
                ['bi-clock-history',  'Full Booking History', 'View all past and upcoming stays.'],
                ['bi-bell',           'Instant Confirmations', 'Receive confirmation immediately.'],
            ] as [$icon, $title, $desc])
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-hotel-gold/20 flex items-center justify-center shrink-0">
                    <i class="bi {{ $icon }} text-hotel-gold text-lg"></i>
                </div>
                <div>
                    <div class="text-white font-semibold text-sm">{{ $title }}</div>
                    <div class="text-white/55 text-xs mt-0.5">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Right Panel: Register Form ──────────────────────────────────── --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10 overflow-y-auto">
        <div class="w-full max-w-md py-8">

            {{-- Mobile Logo --}}
            <div class="lg:hidden flex items-center mb-8">
                <img src="{{ asset('images/logo.png') }}" alt="Dara Meas Hotel" class="h-10 w-auto object-contain">
            </div>

            <h1 class="font-[Playfair_Display] text-3xl font-bold text-hotel-dark mb-1">Create your account</h1>
            <p class="text-gray-500 text-sm mb-6">Register to book rooms and manage your reservations.</p>

            {{-- General Error --}}
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-6 flex items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-red-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            {{-- ── Registration Method Tabs ─────────────────────────────── --}}
            {{--
                Default tab: phone path if validation failed on login_phone field,
                otherwise email path.
            --}}
            @php $defaultTab = $errors->has('login_phone') ? 'phone' : 'email'; @endphp

            <div class="flex rounded-xl border border-gray-200 p-1 mb-6 bg-gray-50" role="tablist">
                <button type="button" id="tab-email" role="tab"
                        onclick="switchTab('email')"
                        class="tab-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200">
                    <i class="bi bi-envelope"></i> Register with Email
                </button>
                <button type="button" id="tab-phone" role="tab"
                        onclick="switchTab('phone')"
                        class="tab-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200">
                    <i class="bi bi-telephone"></i> Register with Phone
                </button>
            </div>

            {{-- ── Shared form — fields toggled by JS ───────────────────── --}}
            <form method="POST" action="{{ route('guest.register.post') }}" class="space-y-5" id="register-form">
                @csrf

                {{-- Full Name (shared) --}}
                <div>
                    <label for="full_name" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                        Full Name <span class="text-red-400">*</span>
                    </label>
                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required autofocus autocomplete="name"
                           placeholder="e.g. Sok Dara"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('full_name') border-red-400 @enderror">
                    @error('full_name')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ── Email-path fields ─────────────────────────────────── --}}
                <div id="email-fields" class="space-y-5">
                    <div>
                        <label for="email" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                            Email Address <span class="text-red-400">*</span>
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('email') border-red-400 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone_contact" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                            Phone Number <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input id="phone_contact" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                               placeholder="e.g. +855 12 345 678"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('phone') border-red-400 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- ── Phone-path fields ─────────────────────────────────── --}}
                <div id="phone-fields" class="space-y-5" style="display:none;">
                    <div>
                        <label for="login_phone" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                            Phone Number <span class="text-red-400">*</span>
                        </label>
                        <input id="login_phone" type="tel" name="login_phone" value="{{ old('login_phone') }}" autocomplete="tel"
                               placeholder="e.g. +855 12 345 678"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('login_phone') border-red-400 @enderror">
                        @error('login_phone')
                            <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                                <i class="bi bi-exclamation-circle"></i> {{ $message }}
                            </p>
                        @enderror
                        <p class="text-xs text-amber-600 mt-1.5">
                            <i class="bi bi-info-circle"></i>
                            You will receive a 6-digit verification code on the next step.
                        </p>
                    </div>
                </div>

                {{-- Gender & Nationality (shared) --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="gender" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                            Gender
                        </label>
                        <select id="gender" name="gender"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('gender') border-red-400 @enderror">
                            <option value="">— Select —</option>
                            <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <p class="text-red-500 text-xs mt-1.5"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nationality" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                            Nationality
                        </label>
                        <input id="nationality" type="text" name="nationality" value="{{ old('nationality') }}"
                               placeholder="e.g. Cambodian"
                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('nationality') border-red-400 @enderror">
                        @error('nationality')
                            <p class="text-red-500 text-xs mt-1.5"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Password (shared) --}}
                <div>
                    <label for="password" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                        Password <span class="text-red-400">*</span>
                    </label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Confirm Password (shared) --}}
                <div>
                    <label for="password_confirmation" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                        Confirm Password <span class="text-red-400">*</span>
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
                </div>

                {{-- Submit --}}
                <button type="submit" id="submit-btn"
                        class="w-full bg-hotel-dark hover:bg-black text-white font-semibold rounded-xl py-3.5 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="bi bi-person-check"></i> <span id="submit-label">Create Account</span>
                </button>
            </form>

            {{-- Login Link --}}
            <p class="text-center text-sm text-gray-500 mt-6">
                Already have an account?
                <a href="{{ route('guest.login') }}" class="text-hotel-dark font-semibold hover:text-hotel-gold transition-colors">
                    Sign in
                </a>
            </p>

        </div>
    </div>

</div>

<script>
(function () {
    const emailFields  = document.getElementById('email-fields');
    const phoneFields  = document.getElementById('phone-fields');
    const emailInput   = document.getElementById('email');
    const loginPhone   = document.getElementById('login_phone');
    const submitLabel  = document.getElementById('submit-label');
    const tabEmail     = document.getElementById('tab-email');
    const tabPhone     = document.getElementById('tab-phone');

    const activeClass   = 'bg-white text-hotel-dark shadow-sm';
    const inactiveClass = 'text-gray-500 hover:text-hotel-dark';

    window.switchTab = function (tab) {
        if (tab === 'email') {
            emailFields.style.display = '';
            phoneFields.style.display  = 'none';
            emailInput.required  = true;
            loginPhone.required  = false;
            submitLabel.textContent = 'Create Account';
            tabEmail.className = 'tab-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 ' + activeClass;
            tabPhone.className  = 'tab-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 ' + inactiveClass;
        } else {
            emailFields.style.display = 'none';
            phoneFields.style.display  = '';
            emailInput.required  = false;
            loginPhone.required  = true;
            submitLabel.textContent = 'Continue to Verify';
            tabPhone.className  = 'tab-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 ' + activeClass;
            tabEmail.className = 'tab-btn flex-1 flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 ' + inactiveClass;
        }
    };

    // Boot with the correct tab (handles validation error repopulation).
    switchTab('{{ $defaultTab }}');
})();
</script>

</body>
</html>
