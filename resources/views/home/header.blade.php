<!-- header inner -->
{{--
   Bootstrap → Tailwind conversion notes:
   - `.container`            → mx-auto w-full max-w-[1170px] px-[15px]  (matches style.css .container max-width)
   - `.row`                 → flex flex-wrap -mx-[15px]                 (Bootstrap row gutters)
   - `.col-*-3` / `.col-*-9`→ sm:w-1/4 / sm:w-3/4 + px-[15px]          (responsive 12-col widths)
   - Bootstrap navbar JS    → Alpine.js (x-data/x-show) for the mobile toggle
   Theme classes (.header, .navigation, .navbar-dark, .nav-link, .logo, …) are kept
   because style.css/responsive.css style them — that is the actual design.
--}}
<div class="header">
<div class="container mx-auto w-full max-w-[1170px] px-[15px]">
    <div class="row flex flex-wrap -mx-[15px]">
        {{-- Logo column: full width on mobile, 25% from the sm breakpoint up --}}
        <div class="logo_section w-full sm:w-1/4 px-[15px]">
            <div class="full">
            <div class="center-desk">
                <div class="logo">
                    <a href="{{ url('/') }}"><img src="images/logo.png" alt="#" /></a>
                </div>
            </div>
            </div>
        </div>
        {{-- Navigation column: 75% from the sm breakpoint up. Alpine `open` state drives the mobile menu --}}
        <div class="w-full sm:w-3/4 px-[15px]" x-data="{ open: false }">
            <nav class="navigation navbar navbar-expand-md navbar-dark flex flex-wrap items-center justify-end relative">
            {{-- Mobile hamburger: visible below the md breakpoint only --}}
            <button class="navbar-toggler md:hidden" type="button" @click="open = !open" aria-controls="navbarsExample04" :aria-expanded="open" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            {{-- Collapsible menu: Alpine x-show toggles it on mobile; `md:!flex` forces it
                 visible (as a row) from md up, regardless of the `open` state. --}}
            <div class="navbar-collapse w-full md:!flex md:w-auto md:basis-auto" id="navbarsExample04" x-show="open" x-cloak>
                <ul class="navbar-nav mr-auto flex flex-col md:flex-row md:items-center list-none m-0 p-0">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.html">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('our_rooms') }}">Our room</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('hotel_gallery') }}">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.html">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('contact_us') }}">Contact Us</a>
                    </li>

                    @if (Route::has('login'))

                            @auth
                                <x-admin-user-menu />

                            @else
                                {{-- Login button --}}
                                <li class="nav-item pr-[10px]">
                                    <a class="inline-block px-3 py-1.5 text-base leading-normal text-center text-white border border-transparent rounded bg-[#28a745] hover:bg-[#218838] transition-colors"
                                    href="{{ url('login') }}">Login</a>
                                </li>

                                @if (Route::has('register'))
                                    {{-- Register button --}}
                                    <li class="nav-item">
                                        <a class="inline-block px-3 py-1.5 text-base leading-normal text-center text-white border border-transparent rounded bg-[#007bff] hover:bg-[#0069d9] transition-colors"
                                        href="{{ url('register') }}">Register</a>
                                    </li>
                                @endif
                            @endauth

                    @endif

                </ul>
            </div>
            </nav>
        </div>
    </div>
</div>
</div>
