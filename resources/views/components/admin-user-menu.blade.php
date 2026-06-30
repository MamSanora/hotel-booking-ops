@auth
    {{--
       Shared user dropdown — rendered in BOTH the home header (.navigation) and the
       admin header (nav.navbar). Bootstrap's dropdown JS is replaced by Alpine.js.
       The `dropdown / dropdown-menu / dropdown-item` class names are kept so the admin
       theme (style.default.css) styles it; the Tailwind utilities provide a clean look
       in the home context (where no theme rules target these classes). Because the theme
       CSS loads after Tailwind, the admin theme wins on shared properties.
    --}}
    <div class="list-inline-item inline-block dropdown relative"
         x-data="{ open: false }" @click.outside="open = false">
        <a href="#"
           @click.prevent="open = !open"
           :aria-expanded="open"
           aria-haspopup="true"
           class="nav-link dropdown-toggle cursor-pointer">
            {{ Auth::user()->name }}
        </a>

        {{-- `:class="{ active: open }"` re-uses the theme's slide-in transition (.dropdown-menu.active) --}}
        <div class="dropdown-menu dropdown-menu-right absolute right-0 z-50 mt-1 min-w-[12rem] rounded border border-gray-200 bg-white py-1 shadow-lg"
             :class="{ 'active': open }"
             x-show="open"
             x-transition.opacity
             x-cloak>
            <a href="{{ route('profile.show') }}"
               class="dropdown-item block px-4 py-2 text-gray-800 hover:bg-gray-100">
                Manage Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                        class="dropdown-item block w-full text-left px-4 py-2 text-gray-800 hover:bg-gray-100">
                    Logout
                </button>
            </form>
        </div>
    </div>
@endauth
