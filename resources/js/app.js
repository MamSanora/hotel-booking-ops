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

// ---------------------------------------------------------------------------
// i18n — Language (EN ↔ ខ្មែរ) & Currency (USD ↔ KHR) switcher.
// Exposes toggleLang(), toggleCurrency(), toggleLocale() on window so that
// any inline onclick handler in Blade templates can call them directly.
// ---------------------------------------------------------------------------
import { initI18n, toggleLang, toggleCurrency } from './i18n';

window.dmhToggleLang     = toggleLang;
window.dmhToggleCurrency = toggleCurrency;

// initI18n() is called automatically in i18n.js on DOMContentLoaded
initI18n();

