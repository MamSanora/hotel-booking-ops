<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Cleaner') — Dara Meas Hotel</title>
    <meta name="description" content="Dara Meas Hotel cleaning staff portal.">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @vite(['resources/css/app.css'])
    @livewireStyles

    @yield('styles')
    @stack('styles')

    <style>
        /* Sidebar transition */
        #clnr-sidebar { transition: transform 0.3s ease; }

        /* Sidebar link active glow — teal/green theme to differentiate from reception gold */
        .clnr-nav-link.active {
            background: rgba(20, 184, 166, 0.15);
            color: #14b8a6;
            border-left: 3px solid #14b8a6;
        }
        .clnr-nav-link {
            border-left: 3px solid transparent;
            transition: all 0.18s ease;
        }
        .clnr-nav-link:hover:not(.active) {
            background: rgba(255,255,255,0.06);
            color: #fff;
        }

        #clnr-main { background: #f4f6f9; }
        #clnr-clock { font-variant-numeric: tabular-nums; }
        #clnr-content { animation: fadeInUp 0.22s ease both; }
        @keyframes fadeInUp {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        #clnr-overlay { transition: opacity 0.25s ease; }
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
    <div id="clnr-overlay"
         x-show="sidebarOpen"
         x-cloak
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <aside id="clnr-sidebar"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed top-0 left-0 h-full w-64 z-40 flex flex-col"
           style="background: linear-gradient(160deg, #0f2027 0%, #203a43 60%, #2c5364 100%);">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-teal-400/20 border border-teal-400/40 flex items-center justify-center shrink-0">
                <i class="bi bi-building text-teal-400 text-lg"></i>
            </div>
            <div>
                <div class="text-white font-bold text-sm leading-tight">Dara Meas Hotel</div>
                <div class="text-teal-400/70 text-[0.65rem] uppercase tracking-widest font-semibold">Cleaner Portal</div>
            </div>
        </div>

        {{-- Staff Badge --}}
        @php $staff = Auth::guard('staff')->user(); @endphp
        <div class="px-6 py-4 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-teal-500/20 border border-teal-400/30 flex items-center justify-center shrink-0">
                    <span class="text-teal-300 font-bold text-sm">{{ strtoupper(substr($staff?->full_name ?? 'C', 0, 1)) }}</span>
                </div>
                <div class="min-w-0">
                    <div class="text-white text-sm font-semibold truncate">{{ $staff?->full_name ?? 'Cleaner' }}</div>
                    <div class="text-white/40 text-[0.65rem] uppercase tracking-widest">Housekeeping</div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">Main</p>

            <a href="{{ route('cleaner.room-check.index') }}"
               class="clnr-nav-link {{ request()->routeIs('cleaner.room-check.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                <i class="bi bi-check2-circle text-base w-5 text-center"></i>
                <span>Room Check</span>
                @livewire('cleaner.room-check-badge')
            </a>

            <div class="pt-4">
                <p class="px-3 text-white/25 text-[0.6rem] uppercase tracking-widest font-bold mb-2">Account</p>

                <a href="{{ route('cleaner.profile.edit') }}"
                   class="clnr-nav-link {{ request()->routeIs('cleaner.profile.*') ? 'active' : 'text-white/60' }} flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
                    <i class="bi bi-person-gear text-base w-5 text-center"></i>
                    <span>Profile Settings</span>
                </a>

                <form method="POST" action="{{ route('staff.logout') }}">
                    @csrf
                    <button type="submit"
                            class="clnr-nav-link text-white/60 hover:text-red-400 w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-r-xl text-sm font-medium">
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
    <div class="lg:ml-64 min-h-screen flex flex-col" id="clnr-main">

        {{-- ── Sticky Top Bar ── --}}
        <header class="sticky top-0 z-20 bg-white dark:bg-[#1a2534] border-b border-gray-200 dark:border-gray-800 shadow-sm dark:shadow-none shrink-0 transition-colors duration-200">
            <div class="flex items-center justify-between px-4 md:px-6 h-16">

                {{-- Left: Mobile hamburger + page title --}}
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden text-gray-500 hover:text-gray-800 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-gray-800 font-semibold text-[0.95rem] leading-tight dark:text-gray-100">
                            @yield('page_title', 'Cleaner Dashboard')
                        </h2>
                        <div class="text-gray-400 text-xs hidden sm:block">{{ now()->format('l, F j, Y') }}</div>
                    </div>
                </div>

                {{-- Right: Clock & Dark Mode --}}
                <div class="flex items-center gap-3">
                    <button onclick="toggleDarkMode()" class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 flex items-center justify-center transition-colors">
                        <i class="bi bi-moon-stars-fill block dark:hidden"></i>
                        <i class="bi bi-sun-fill hidden dark:block text-teal-400"></i>
                    </button>

                    {{-- Live clock --}}
                    <div class="hidden sm:flex items-center gap-2 bg-gray-100 dark:bg-black/20 border border-gray-200 dark:border-white/5 rounded-xl px-4 py-2">
                        <i class="bi bi-clock text-teal-500 text-sm"></i>
                        <span id="clnr-clock" class="font-mono text-gray-800 dark:text-gray-100 font-bold text-sm tracking-wide">--:--:--</span>
                        <span id="clnr-ampm" class="text-gray-400 text-xs font-medium"></span>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── Flash Messages ── --}}
        @if(session('success'))
            <div class="mx-6 mt-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <i class="bi bi-exclamation-circle-fill text-red-500"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- ── Page Content ── --}}
        <main class="flex-1" id="clnr-content">
            @yield('content')
        </main>
    </div>

    <script>
        function updateClock() {
            const now  = new Date();
            let h      = now.getHours();
            const m    = String(now.getMinutes()).padStart(2, '0');
            const s    = String(now.getSeconds()).padStart(2, '0');
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            const el = document.getElementById('clnr-clock');
            const am = document.getElementById('clnr-ampm');
            if (el) el.textContent = `${String(h).padStart(2,'0')}:${m}:${s}`;
            if (am) am.textContent = ampm;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

    @yield('scripts')
    @stack('scripts')
    @livewireScripts
</body>
</html>
