<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Dara Meas Hotel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="min-h-screen bg-hotel-dark font-[Inter]">

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-white rounded-[20px] shadow-[0_24px_60px_rgba(0,0,0,0.3)] p-8 md:p-10">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-hotel-dark to-hotel-accent rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="bi bi-shield-lock-fill text-hotel-gold text-2xl"></i>
                </div>
                <h1 class="font-[Playfair_Display] text-2xl font-bold text-hotel-dark">Administrator Login</h1>
                <p class="text-gray-500 text-sm mt-1">Dara Meas Hotel — Staff Portal</p>
            </div>

            {{-- Error Alert --}}
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-6 flex items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-red-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                @csrf

                {{-- Username --}}
                <div>
                    <label for="username" class="block text-[0.78rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="bi bi-person"></i>
                        </span>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username"
                               class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('username') border-red-400 @enderror">
                    </div>
                    @error('username')
                        <p class="text-red-500 text-xs mt-1.5"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-[0.78rem] uppercase font-semibold text-gray-500 tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input id="password" type="password" name="password" required
                               class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:border-hotel-gold focus:ring-[3px] focus:ring-hotel-gold/15 outline-none transition-all @error('password') border-red-400 @enderror">
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1.5"><i class="bi bi-exclamation-circle"></i> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-hotel-dark hover:bg-black text-white font-semibold rounded-xl py-3.5 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2 mt-2">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In as Administrator
                </button>
            </form>

            {{-- Links --}}
            <div class="mt-6 pt-6 border-t border-gray-100 flex justify-between text-xs text-gray-400">
                <a href="{{ url('/') }}" class="hover:text-hotel-dark transition-colors flex items-center gap-1">
                    <i class="bi bi-house"></i> Back to Hotel Site
                </a>
                <a href="{{ route('reception.login') }}" class="hover:text-hotel-dark transition-colors flex items-center gap-1">
                    <i class="bi bi-person-badge"></i> Reception Login
                </a>
            </div>

        </div>

    </div>
</div>

</body>
</html>
