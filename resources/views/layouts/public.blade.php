<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dara Meas Hotel') — Phnom Penh, Cambodia</title>
    <meta name="description" content="@yield('meta_description', 'Book your stay at Dara Meas Hotel, a comfortable 2.5-star hotel in the heart of Phnom Penh, Cambodia.')">

    <!-- Fonts: Inter and Playfair Display -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons (We keep these as they are just an icon font library) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS (via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Custom Styles injected by individual views -->
    @yield('styles')
    @stack('styles')
</head>
<body class="font-sans antialiased text-gray-800 bg-white flex flex-col min-h-screen">

    @php
        $currentUser = null;
        $currentGuard = null;

        if (Auth::guard('admin')->check()) {
            $currentUser = Auth::guard('admin')->user();
            $currentGuard = 'admin';
        } elseif (Auth::guard('staff')->check()) {
            $currentUser = Auth::guard('staff')->user();
            $currentGuard = 'staff';
        } elseif (Auth::guard('web')->check()) {
            $currentUser = Auth::guard('web')->user();
            $currentGuard = 'web';
        }

        // Display name differs by guard: admins/staff have full_name; guests resolve through GuestAuth → Guest.
        $displayName = match ($currentGuard) {
            'admin', 'staff' => $currentUser?->full_name ?? '—',
            'web'            => $currentUser?->guest?->full_name ?? $currentUser?->email ?? '—',
            default          => '',
        };

        $logoutRoute = 'guest.logout';
        if ($currentGuard === 'admin') $logoutRoute = 'admin.logout';
        if ($currentGuard === 'staff')  $logoutRoute = 'reception.logout';
    @endphp

    <!-- ==========================================
         NAVBAR
         ========================================== -->
    <nav x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-50 bg-gradient-to-br from-hotel-dark to-hotel-accent shadow-lg py-3">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex items-center justify-between">
                
                <!-- Brand Logo -->
                <a href="{{ url('/') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Dara Meas Hotel" class="h-10 w-auto object-contain">
                </a>

                <!-- Desktop Navigation Links -->
                <div class="hidden lg:flex items-center space-x-2 xl:space-x-4">
                    <a href="{{ url('/') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('/') ? 'text-hotel-gold bg-hotel-gold/10' : 'text-white/85 hover:text-hotel-gold hover:bg-hotel-gold/10' }}">
                        <i class="bi bi-house mr-1"></i> Home
                    </a>
                    <a href="{{ route('rooms.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('rooms*') ? 'text-hotel-gold bg-hotel-gold/10' : 'text-white/85 hover:text-hotel-gold hover:bg-hotel-gold/10' }}">
                        <i class="bi bi-door-open mr-1"></i> Rooms
                    </a>
                    <a href="{{ route('about') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('about*') ? 'text-hotel-gold bg-hotel-gold/10' : 'text-white/85 hover:text-hotel-gold hover:bg-hotel-gold/10' }}">
                        <i class="bi bi-info-circle mr-1"></i> About
                    </a>
                    <a href="{{ route('contact') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200 {{ request()->is('contact*') ? 'text-hotel-gold bg-hotel-gold/10' : 'text-white/85 hover:text-hotel-gold hover:bg-hotel-gold/10' }}">
                        <i class="bi bi-envelope mr-1"></i> Contact
                    </a>
                </div>

                <!-- Desktop Auth Section -->
                <div class="hidden lg:flex items-center space-x-3">
                    @if($currentUser)
                        <!-- User Dropdown Menu using Alpine.js -->
                        <div x-data="{ dropdownOpen: false }" class="relative">
                            <button @click="dropdownOpen = !dropdownOpen" @click.outside="dropdownOpen = false" class="flex items-center gap-2 text-white/85 hover:text-white focus:outline-none transition-colors">
                                <i class="bi bi-person-circle text-hotel-gold text-lg"></i>
                                <span class="font-medium text-sm">{{ $displayName }}</span>
                                <i class="bi bi-chevron-down text-xs ml-1"></i>
                            </button>

                            <!-- Dropdown content -->
                            <div x-show="dropdownOpen"
                                 x-transition.opacity.duration.200ms
                                 class="absolute right-0 mt-3 w-56 bg-white rounded-lg shadow-xl py-2 z-50 border border-gray-100"
                                 style="display: none;">

                                @if($currentGuard === 'admin')
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-hotel-dark transition-colors">
                                        <i class="bi bi-speedometer2 text-blue-600 mr-2"></i> Admin Dashboard
                                    </a>
                                @elseif($currentGuard === 'staff')
                                    <a href="{{ route('reception.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-hotel-dark transition-colors">
                                        <i class="bi bi-reception-4 text-green-600 mr-2"></i> Reception Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('guest.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-hotel-dark transition-colors">
                                        <i class="bi bi-calendar-check text-blue-500 mr-2"></i> My Bookings
                                    </a>
                                    <a href="{{ route('guest.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-hotel-dark transition-colors">
                                        <i class="bi bi-person-gear text-gray-500 mr-2"></i> My Profile
                                    </a>
                                @endif

                                <hr class="my-1 border-gray-100">

                                <form method="POST" action="{{ route($logoutRoute) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="bi bi-box-arrow-right mr-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('guest.login') }}" class="px-4 py-1.5 rounded-full border-[1.5px] border-hotel-gold text-hotel-gold text-sm font-medium hover:bg-hotel-gold hover:text-hotel-dark transition-colors duration-200">
                            <i class="bi bi-box-arrow-in-right mr-1"></i> Login
                        </a>
                        @if (Route::has('guest.register'))
                            <a href="{{ route('guest.register') }}" class="px-4 py-1.5 rounded-full bg-hotel-gold text-hotel-dark text-sm font-bold hover:bg-hotel-gold-hover transition-colors duration-200">
                                <i class="bi bi-person-plus mr-1"></i> Register
                            </a>
                        @endif
                    @endif
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-hotel-gold p-2 focus:outline-none">
                    <i class="bi" :class="mobileMenuOpen ? 'bi-x-lg text-xl' : 'bi-list text-2xl'"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu (Collapsible) -->
        <div x-show="mobileMenuOpen" x-transition.opacity class="lg:hidden bg-hotel-accent border-t border-white/10 mt-3" style="display: none;">
            <div class="px-4 py-4 space-y-2">
                <a href="{{ url('/') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('/') ? 'text-hotel-gold bg-white/5' : 'text-white/85' }}">Home</a>
                <a href="{{ route('rooms.index') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('rooms*') ? 'text-hotel-gold bg-white/5' : 'text-white/85' }}">Rooms</a>
                <a href="{{ route('about') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('about*') ? 'text-hotel-gold bg-white/5' : 'text-white/85' }}">About</a>
                <a href="{{ route('contact') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->is('contact*') ? 'text-hotel-gold bg-white/5' : 'text-white/85' }}">Contact</a>

                <hr class="border-white/10 my-3">

                @if($currentUser)
                    <div class="px-3 py-2 text-hotel-gold font-medium">Hello, {{ $displayName }}</div>
                    @if($currentGuard === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white/85">Admin Dashboard</a>
                    @elseif($currentGuard === 'staff')
                        <a href="{{ route('reception.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white/85">Reception Dashboard</a>
                    @else
                        <a href="{{ route('guest.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-white/85">My Bookings</a>
                    @endif
                    <form method="POST" action="{{ route($logoutRoute) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-400">Logout</button>
                    </form>
                @else
                    <div class="flex flex-col space-y-2 pt-2">
                        <a href="{{ route('guest.login') }}" class="text-center px-4 py-2 rounded-md border border-hotel-gold text-hotel-gold font-medium">Login</a>
                        @if (Route::has('guest.register'))
                            <a href="{{ route('guest.register') }}" class="text-center px-4 py-2 rounded-md bg-hotel-gold text-hotel-dark font-bold">Register</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- ==========================================
         FLASH MESSAGES
         ========================================== -->
    @if(session('success') || session('error') || session('message'))
        <div class="container mx-auto px-4 mt-6">
            @if(session('success'))
                <div class="bg-green-50 text-green-800 border border-green-200 rounded-lg p-4 flex items-start shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-check-circle-fill text-green-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ session('success') }}</div>
                    <button @click="show = false" class="text-green-500 hover:text-green-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 text-red-800 border border-red-200 rounded-lg p-4 flex items-start shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ session('error') }}</div>
                    <button @click="show = false" class="text-red-500 hover:text-red-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif
            @if(session('message'))
                <div class="bg-blue-50 text-blue-800 border border-blue-200 rounded-lg p-4 flex items-start shadow-sm mb-4" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-info-circle-fill text-blue-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ session('message') }}</div>
                    <button @click="show = false" class="text-blue-500 hover:text-blue-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif
        </div>
    @endif

    <!-- ==========================================
         MAIN PAGE CONTENT
         ========================================== -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- ==========================================
         FOOTER
         ========================================== -->
    <footer class="bg-hotel-dark text-white/75 pt-12 pb-6 mt-16">
        <div class="container mx-auto px-4 md:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-6">
                
                <!-- Hotel Info -->
                <div class="lg:col-span-4">
                    <h5 class="text-hotel-gold font-playfair font-semibold text-lg mb-4 flex items-center">
                        <i class="bi bi-building mr-2"></i> Dara Meas Hotel
                    </h5>
                    <p class="text-sm leading-relaxed mb-6">
                        A comfortable 2.5-star hotel in Phnom Penh, Cambodia.<br>
                        Established 2019. 47 rooms across 4 floors.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-white/65 hover:text-hotel-gold transition-colors text-xl"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white/65 hover:text-hotel-gold transition-colors text-xl"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white/65 hover:text-hotel-gold transition-colors text-xl"><i class="bi bi-telegram"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="lg:col-span-2 md:col-span-1">
                    <h5 class="text-hotel-gold font-playfair font-semibold text-lg mb-4">Quick Links</h5>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ url('/') }}" class="hover:text-hotel-gold transition-colors">Home</a></li>
                        <li><a href="{{ route('rooms.index') }}" class="hover:text-hotel-gold transition-colors">Our Rooms</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-hotel-gold transition-colors">About Us</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-hotel-gold transition-colors">Contact</a></li>
                    </ul>
                </div>

                <!-- Room Types -->
                <div class="lg:col-span-2 md:col-span-1">
                    <h5 class="text-hotel-gold font-playfair font-semibold text-lg mb-4">Room Types</h5>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('rooms.index') }}" class="hover:text-hotel-gold transition-colors">Standard Twin</a></li>
                        <li><a href="{{ route('rooms.index') }}" class="hover:text-hotel-gold transition-colors">Standard Double</a></li>
                        <li><a href="{{ route('rooms.index') }}" class="hover:text-hotel-gold transition-colors">Deluxe Double</a></li>
                        <li><a href="{{ route('rooms.index') }}" class="hover:text-hotel-gold transition-colors">Family Room</a></li>
                        <li><a href="{{ route('rooms.index') }}" class="hover:text-hotel-gold transition-colors">Suite</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="lg:col-span-4">
                    <h5 class="text-hotel-gold font-playfair font-semibold text-lg mb-4">Contact Us</h5>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start">
                            <i class="bi bi-geo-alt text-hotel-gold mr-3 mt-0.5"></i> 
                            <span>Phnom Penh, Cambodia</span>
                        </li>
                        <li class="flex items-start">
                            <i class="bi bi-telephone text-hotel-gold mr-3 mt-0.5"></i> 
                            <span>+855 23 456 789</span>
                        </li>
                        <li class="flex items-start">
                            <i class="bi bi-envelope text-hotel-gold mr-3 mt-0.5"></i> 
                            <span>info@darameashotel.com</span>
                        </li>
                        <li class="flex items-start">
                            <i class="bi bi-clock text-hotel-gold mr-3 mt-0.5"></i> 
                            <span>24/7 Front Desk</span>
                        </li>
                    </ul>
                </div>

            </div>

            <hr class="border-white/10 mt-10 mb-6">
            
            <p class="text-center text-white/40 text-sm">
                &copy; {{ date('Y') }} Dara Meas Hotel. All rights reserved. <span class="mx-2">&middot;</span> Phnom Penh, Cambodia
            </p>
        </div>
    </footer>

    <!-- Any extra JS scripts injected by views -->
    @stack('scripts')
</body>
</html>
