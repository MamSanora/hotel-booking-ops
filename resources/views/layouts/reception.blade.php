<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Reception') — Dara Meas Hotel</title>
    <meta name="description" content="Dara Meas Hotel front-desk management portal.">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS (Livewire auto-injects Alpine.js and Livewire scripts) -->
    @vite(['resources/css/app.css'])
    @livewireStyles

    @yield('styles')
    @stack('styles')

    <style>
        /* Sidebar transition */
        #rcpt-sidebar { transition: transform 0.3s ease; }

        /* Sidebar link active glow */
        .rcpt-nav-link.active {
            background: rgba(200, 169, 110, 0.15);
            color: #c8a96e;
            border-left: 3px solid #c8a96e;
        }
        .rcpt-nav-link {
            border-left: 3px solid transparent;
            transition: all 0.18s ease;
        }
        .rcpt-nav-link:hover:not(.active) {
            background: rgba(255,255,255,0.06);
            color: #fff;
        }

        /* Subtle main content bg */
        #rcpt-main { background: #f4f6f9; }

        /* Live clock mono */
        #rcpt-clock { font-variant-numeric: tabular-nums; }

        /* Page-level transitions */
        #rcpt-content { animation: fadeInUp 0.22s ease both; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Mobile overlay */
        #rcpt-overlay { transition: opacity 0.25s ease; }

        /* Hide Alpine-cloaked elements until Alpine processes them */
        [x-cloak] { display: none !important; }
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
<body class="font-sans antialiased bg-[#f4f6f9] dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-200" x-data="{ sidebarOpen: false }">

    {{-- =====================================================
         SIDEBAR
         ===================================================== --}}
    {{-- Mobile overlay --}}
    <div id="rcpt-overlay"
         x-show="sidebarOpen"
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <aside id="rcpt-sidebar"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed top-0 left-0 h-full w-64 z-40 flex flex-col"
           style="background: linear-gradient(160deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-hotel-gold/20 border border-hotel-gold/40 flex items-center justify-center shrink-0">
                <i class="bi bi-building text-hotel-gold text-lg"></i>
            </div>
            <div>
                <div class="text-white font-bold text-sm leading-tight">Dara Meas Hotel</div>
                <div class="text-hotel-gold/70 text-[0.65rem] uppercase tracking-widest font-semibold">Reception Portal</div>
            </div>
        </div>

        {{-- Staff Badge --}}
        @php $staff = Auth::guard('staff')->user(); @endphp
        <div class="px-6 py-4 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-teal-500/20 border border-teal-400/30 flex items-center justify-center shrink-0">
                    <span class="text-teal-300 font-bold text-sm">{{ strtoupper(substr($staff?->full_name ?? 'S', 0, 1)) }}</span>
                </div>
                <div class="min-w-0">
                    <div class="text-white text-sm font-semibold truncate">{{ $staff?->full_name ?? 'Staff' }}</div>
                    <div class="text-white/40 text-[0.65rem] uppercase tracking-widest">Receptionist</div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
            <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">Main</p>

            <a href="{{ route('reception.dashboard') }}"
               class="rcpt-nav-link {{ request()->routeIs('reception.dashboard') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                <i class="bi bi-speedometer2 text-base w-5 text-center"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('reception.manual-booking.create') }}"
               class="rcpt-nav-link {{ request()->routeIs('reception.manual-booking.create') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                <i class="bi bi-calendar-plus text-base w-5 text-center"></i>
                <span>New Booking</span>
            </a>
            
            <a href="{{ route('reception.manage-bookings.index') }}"
               class="rcpt-nav-link {{ request()->routeIs('reception.manage-bookings.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                <i class="bi bi-journal-text text-base w-5 text-center"></i>
                <span>Manage Bookings</span>
            </a>

            <a href="{{ route('reception.room-check.index') }}"
               class="rcpt-nav-link {{ request()->routeIs('reception.room-check.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                <i class="bi bi-check2-all text-base w-5 text-center"></i>
                <span>Room Check</span>
                @php
                    $pendingClean = \App\Models\Room::whereIn('current_status', ['cleaning', 'maintenance'])->count();
                @endphp
                @if($pendingClean > 0)
                    <span class="ml-auto bg-amber-500 text-white text-[0.6rem] font-bold px-1.5 py-0.5 rounded-full leading-none">{{ $pendingClean }}</span>
                @endif
            </a>


            <div class="pt-4">
                <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">System</p>

                <a href="{{ route('reception.profile.edit') }}"
                   class="rcpt-nav-link {{ request()->routeIs('reception.profile.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-person-gear text-base w-5 text-center"></i>
                    <span>Profile Settings</span>
                </a>

                <form method="POST" action="{{ route('reception.logout') }}">
                    @csrf
                    <button type="submit"
                            class="rcpt-nav-link text-white/60 hover:text-red-400 w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
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
    <div class="lg:ml-64 min-h-screen flex flex-col" id="rcpt-main">

        {{-- ── Sticky Top Bar ── --}}
        <header class="sticky top-0 z-20 bg-white dark:bg-[#1a2534] border-b border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-none shrink-0 transition-colors duration-200">
            <div class="flex items-center justify-between px-4 md:px-6 h-16">

                {{-- Left: Mobile hamburger + page title --}}
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden text-gray-500 hover:text-hotel-dark p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-hotel-dark font-semibold text-[0.95rem] leading-tight">
                            @yield('page_title', 'Reception Desk')
                        </h2>
                        <div class="text-gray-400 text-xs hidden sm:block" id="rcpt-date">{{ now()->format('l, F j, Y') }}</div>
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
                        <i class="bi bi-clock text-hotel-gold text-sm"></i>
                        <span id="rcpt-clock" class="font-mono text-hotel-dark font-bold text-sm tracking-wide">--:--:--</span>
                        <span id="rcpt-ampm" class="text-gray-400 text-xs font-medium"></span>
                    </div>

                    {{-- New Walk-In quick button --}}
                    <a href="{{ route('reception.manual-booking.create') }}" class="flex items-center gap-2 bg-hotel-gold text-hotel-dark px-4 py-2 rounded-xl font-bold hover:bg-yellow-500 transition-colors shadow-sm">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>New Booking</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- ── Page Content ── --}}
        <main class="flex-1" id="rcpt-content">
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
            const el = document.getElementById('rcpt-clock');
            const am = document.getElementById('rcpt-ampm');
            if (el) el.textContent = `${String(h).padStart(2,'0')}:${m}:${s}`;
            if (am) am.textContent = ampm;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

    @yield('scripts')
    @stack('scripts')
    <x-global-confirm />
</body>
</html>
