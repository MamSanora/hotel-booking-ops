import './bootstrap';

// ---------------------------------------------------------------------------
// Alpine.js — replaces Bootstrap's JavaScript plugins (dropdowns, collapse,
// carousel, navbar toggler) with a lightweight, Tailwind-friendly runtime.
// These standalone Blade pages (home + admin themes) do not load Livewire,
// so Alpine is registered manually here and exposed on `window` for debugging.
// ---------------------------------------------------------------------------
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse'; // smooth height animation for collapsible menus

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
