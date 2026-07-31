<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') — Dara Meas Hotel</title>
    <meta name="description" content="Dara Meas Hotel administrator portal.">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS + Alpine.js via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
    @stack('styles')

    <style>
        /* Sidebar transition */
        #admin-sidebar { transition: transform 0.3s ease; }

        /* Sidebar link active glow */
        .admin-nav-link.active {
            background: rgba(200, 169, 110, 0.15);
            color: #c8a96e;
            border-left: 3px solid #c8a96e;
        }
        .admin-nav-link {
            border-left: 3px solid transparent;
            transition: all 0.18s ease;
        }
        .admin-nav-link:hover:not(.active) {
            background: rgba(255,255,255,0.06);
            color: #fff;
        }

        /* Subtle main content bg */
        #admin-main { background: #f8fafc; }

        /* Live clock mono */
        #admin-clock { font-variant-numeric: tabular-nums; }

        /* Page-level transitions */
        #admin-content { animation: fadeInUp 0.22s ease both; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Mobile overlay */
        #admin-overlay { transition: opacity 0.25s ease; }
    </style>

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-[#f8f9fa] dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-200" x-data="{ sidebarOpen: false }">

    {{-- =====================================================
         SIDEBAR
         ===================================================== --}}
    {{-- Mobile overlay --}}
    <div id="admin-overlay"
         x-show="sidebarOpen"
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <aside id="admin-sidebar"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed top-0 left-0 h-full w-64 z-40 flex flex-col bg-hotel-dark shadow-2xl">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-hotel-gold/20 border border-hotel-gold/40 flex items-center justify-center shrink-0">
                <i class="bi bi-shield-lock-fill text-hotel-gold text-lg"></i>
            </div>
            <div>
                <div class="text-white font-bold text-sm leading-tight">Dara Meas Hotel</div>
                <div class="text-hotel-gold/70 text-[0.65rem] uppercase tracking-widest font-semibold">Admin Portal</div>
            </div>
        </div>

        {{-- Admin Badge --}}
        @php $admin = Auth::guard('admin')->user(); @endphp
        <div class="px-6 py-4 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-500/20 border border-blue-400/30 flex items-center justify-center shrink-0">
                    <span class="text-blue-300 font-bold text-sm">{{ strtoupper(substr($admin?->full_name ?? 'A', 0, 1)) }}</span>
                </div>
                <div class="min-w-0">
                    <div class="text-white text-sm font-semibold truncate">{{ $admin?->full_name ?? 'Administrator' }}</div>
                    <div class="text-white/40 text-[0.65rem] uppercase tracking-widest">Super Admin</div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
            <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">Overview</p>

            <a href="{{ route('admin.dashboard') }}"
               class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                <i class="bi bi-speedometer2 text-base w-5 text-center"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-4">
                <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">Hotel Management</p>

                <a href="{{ route('admin.bookings.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-calendar-check text-base w-5 text-center"></i>
                    <span>Bookings</span>
                </a>

                <a href="{{ route('admin.rooms.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-door-closed text-base w-5 text-center"></i>
                    <span>Rooms</span>
                </a>

                <a href="{{ route('admin.room-types.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.room-types.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-tags text-base w-5 text-center"></i>
                    <span>Room Types</span>
                </a>
            </div>

            <div class="pt-4">
                <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">Operations</p>

                <a href="{{ route('admin.guests.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.guests.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-person-lines-fill text-base w-5 text-center"></i>
                    <span>Guests List</span>
                </a>

                <a href="{{ route('admin.messages.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.messages.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-chat-left-dots text-base w-5 text-center"></i>
                    <span>Messages</span>
                </a>

                <a href="{{ route('admin.gallery.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-images text-base w-5 text-center"></i>
                    <span>Gallery</span>
                </a>
            </div>

            <div class="pt-4">
                <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">Account Management</p>

                <a href="{{ route('admin.guest-accounts.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.guest-accounts.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-person-check text-base w-5 text-center"></i>
                    <span>Guest Accounts</span>
                </a>

                <a href="{{ route('admin.staff.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-people text-base w-5 text-center"></i>
                    <span>Staff Accounts</span>
                </a>

                @if(auth('admin')->user()->isSuperAdmin())
                <a href="{{ route('admin.admins.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-shield-lock text-base w-5 text-center"></i>
                    <span>Admin Accounts</span>
                </a>
                @endif
            </div>

            <div class="pt-4">
                <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">Settings</p>

                {{-- Payment Gateways nav hidden for production deployment (Point 13) --}}
                {{-- <a href="{{ route('admin.payment-gateways.index') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.payment-gateways.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-credit-card text-base w-5 text-center"></i>
                    <span>Payment Gateways</span>
                </a> --}}

                <a href="{{ route('admin.profile.edit') }}"
                   class="admin-nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-person-gear text-base w-5 text-center"></i>
                    <span>Profile Settings</span>
                </a>


                <form method="POST" action="{{ route('admin.logout') }}" class="mt-4 border-t border-white/5 pt-2">
                    @csrf
                    <button type="submit"
                            class="admin-nav-link text-white/60 hover:text-red-400 w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                        <i class="bi bi-box-arrow-left text-base w-5 text-center"></i>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </nav>

        {{-- Bottom: Hotel link --}}
        <div class="px-6 py-4 border-t border-white/10 shrink-0">
            <a href="{{ url('/') }}" target="_blank"
               class="flex items-center gap-2 text-white/30 hover:text-white/60 text-xs transition-colors">
                <i class="bi bi-globe2"></i>
                <span>View Hotel Website</span>
            </a>
        </div>
    </aside>

    {{-- =====================================================
         MAIN AREA (right of sidebar)
         ===================================================== --}}
    <div class="lg:ml-64 min-h-screen flex flex-col" id="admin-main">

        {{-- ── Sticky Top Bar ── --}}
        <header class="sticky top-0 z-20 bg-white dark:bg-[#1a2534] border-b border-gray-200 dark:border-gray-800 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] dark:shadow-none shrink-0 transition-colors duration-200">
            <div class="flex items-center justify-between px-4 md:px-6 h-16">

                {{-- Left: Mobile hamburger + page title --}}
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden text-gray-500 hover:text-hotel-dark p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-hotel-dark font-semibold text-[0.95rem] leading-tight">
                            @yield('page_title', 'Admin Dashboard')
                        </h2>
                        <div class="text-gray-400 text-xs hidden sm:block" id="admin-date">{{ now()->format('l, F j, Y') }}</div>
                    </div>
                </div>

                {{-- Right: Clock & Dark Mode --}}
                <div class="flex items-center gap-3">
                    {{-- Dark Mode Toggle --}}
                    <button onclick="toggleDarkMode()" class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center transition-colors">
                        <i class="bi bi-moon-stars-fill block dark:hidden"></i>
                        <i class="bi bi-sun-fill hidden dark:block text-hotel-gold"></i>
                    </button>

                    {{-- Live clock --}}
                    <div class="hidden sm:flex items-center gap-2 bg-hotel-dark/5 dark:bg-black/20 border border-hotel-dark/10 dark:border-white/5 rounded-xl px-4 py-2">
                        <i class="bi bi-clock text-blue-500 dark:text-blue-400 text-sm"></i>
                        <span id="admin-clock" class="font-mono text-hotel-dark font-bold text-sm tracking-wide">--:--:--</span>
                        <span id="admin-ampm" class="text-gray-400 text-xs font-medium"></span>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── Page Content ── --}}
        <main class="flex-1" id="admin-content">
            {{-- Flash Messages (Global) --}}
            @if(session('success'))
                <div class="m-6 bg-green-50 text-green-800 border border-green-200 rounded-lg p-4 flex items-start shadow-sm" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-check-circle-fill text-green-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ session('success') }}</div>
                    <button @click="show = false" class="text-green-500 hover:text-green-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif
            @if(session('error'))
                <div class="m-6 bg-red-50 text-red-800 border border-red-200 rounded-lg p-4 flex items-start shadow-sm" x-data="{ show: true }" x-show="show">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">{{ session('error') }}</div>
                    <button @click="show = false" class="text-red-500 hover:text-red-700 focus:outline-none"><i class="bi bi-x-lg"></i></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    {{-- =====================================================
         LIVE CLOCK SCRIPT
         ===================================================== --}}
    <script>
        function updateClock() {
            const now  = new Date();
            let h      = now.getHours();
            const m    = String(now.getMinutes()).padStart(2, '0');
            const s    = String(now.getSeconds()).padStart(2, '0');
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            const el = document.getElementById('admin-clock');
            const am = document.getElementById('admin-ampm');
            if (el) el.textContent = `${String(h).padStart(2,'0')}:${m}:${s}`;
            if (am) am.textContent = ampm;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

    @yield('scripts')
    @stack('scripts')
    
    @stack('modals')
    @livewireScripts
    <x-global-confirm />
</body>
</html>
