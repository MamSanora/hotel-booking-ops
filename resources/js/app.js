import './bootstrap';

// ---------------------------------------------------------------------------
// Alpine.js — replaces Bootstrap's JavaScript plugins (dropdowns, collapse,
// carousel, navbar toggler) with a lightweight, Tailwind-friendly runtime.
//
// IMPORTANT: Livewire 3 automatically initializes and starts Alpine on pages
// where @livewireScripts is present. Starting Alpine manually BEFORE Livewire
// breaks wire:model.live and all Livewire reactivity.
// We only call Alpine.start() manually on non-Livewire pages.
// ---------------------------------------------------------------------------
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);

// Only start Alpine manually if Livewire has not already taken control.
// On pages with @livewireScripts, Livewire 3 will call Alpine.start() itself.
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Livewire) {
        Alpine.start();
    }
});

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

