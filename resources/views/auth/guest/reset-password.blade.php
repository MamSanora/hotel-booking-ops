<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — Dara Meas Hotel</title>
    <meta name="description" content="Set a new password for your account.">
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
            <div class="w-16 h-16 rounded-full bg-hotel-gold/10 flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-key text-3xl text-hotel-gold"></i>
            </div>
            <h1 class="font-[Playfair_Display] text-2xl font-bold text-hotel-dark mb-2">Create New Password</h1>
            <p class="text-sm text-gray-500 leading-relaxed">
                Your identity has been verified. Please set a strong new password for your account.
            </p>
        </div>

        <form method="POST" action="{{ route('guest.forgot-password.reset.submit') }}" class="space-y-6">
            @csrf

            {{-- New Password --}}
            <div>
                <label for="password" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                    New Password
                </label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-[0.8rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">
                    Confirm Password
                </label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all">
            </div>

            <button type="submit"
                    class="w-full bg-hotel-dark hover:bg-black text-white font-semibold rounded-xl py-3.5 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
                <i class="bi bi-check2-circle"></i> Save and Sign In
            </button>
        </form>

    </div>
</div>

</body>
</html>
