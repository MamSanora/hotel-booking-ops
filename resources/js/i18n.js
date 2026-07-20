/**
 * i18n.js — Dara Meas Hotel Language & Currency Switcher
 *
 * Approach: purely client-side, no page reload needed.
 *   • Language (EN ↔ ខ្មែរ): elements carry data-en="..." data-km="..." attributes.
 *     On switch, their textContent is swapped.
 *   • Currency (USD ↔ KHR): elements carry data-price-usd="<number>" attributes.
 *     On switch, we render the converted amount in KHR (1 USD = 4100 KHR).
 *   • Both preferences persist in localStorage across page loads.
 */

const KHR_RATE = 4100;

// ─── State ────────────────────────────────────────────────────────────────────
function getSavedLang()     { return localStorage.getItem('dmh_lang')     || 'en'; }
function getSavedCurrency() { return localStorage.getItem('dmh_currency') || 'usd'; }

function saveLang(lang)         { localStorage.setItem('dmh_lang',     lang); }
function saveCurrency(currency) { localStorage.setItem('dmh_currency', currency); }

// ─── Translation Apply ────────────────────────────────────────────────────────
function applyLang(lang) {
    document.querySelectorAll('[data-en]').forEach(el => {
        el.textContent = lang === 'km' ? (el.dataset.km || el.dataset.en) : el.dataset.en;
    });
    // Update html lang attribute for accessibility
    document.documentElement.lang = lang === 'km' ? 'km' : 'en';
}

function applyCurrency(currency) {
    document.querySelectorAll('[data-price-usd]').forEach(el => {
        const usd = parseFloat(el.dataset.priceUsd);
        if (isNaN(usd)) return;
        if (currency === 'khr') {
            const khr = Math.round(usd * KHR_RATE);
            el.textContent = '៛' + khr.toLocaleString('en-US');
            el.dataset.displayCurrency = 'khr';
        } else {
            el.textContent = '$' + usd.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            el.dataset.displayCurrency = 'usd';
        }
    });

    // Also update /night label visibility if present
    document.querySelectorAll('[data-night-label]').forEach(el => {
        el.textContent = currency === 'khr'
            ? (el.dataset.nightLabelKm || '/យប់')
            : '/night';
    });
}

// ─── Navbar pill updater ──────────────────────────────────────────────────────
function updatePill(lang, currency) {
    // Show the CURRENT active language in the pill
    const langSpan = document.getElementById('pill-lang-active');
    if (langSpan) {
        langSpan.textContent = lang === 'km' ? 'ខ្មែរ' : 'EN';
    }

    // Show the CURRENT active currency in the pill
    const currencySpan = document.getElementById('pill-currency-active');
    if (currencySpan) {
        currencySpan.textContent = currency === 'khr' ? 'KHR (៛)' : 'USD ($)';
    }
}

// ─── Toggle handlers ──────────────────────────────────────────────────────────
export function toggleLang() {
    const current = getSavedLang();
    const next = current === 'en' ? 'km' : 'en';
    saveLang(next);
    applyLang(next);
    updatePill(next, getSavedCurrency());
}

export function toggleCurrency() {
    const current = getSavedCurrency();
    const next = current === 'usd' ? 'khr' : 'usd';
    saveCurrency(next);
    applyCurrency(next);
    updatePill(getSavedLang(), next);
}

export function toggleLocale() {
    // Single-button toggle: click cycles lang first, then currency together
    toggleLang();
    toggleCurrency();
}

// ─── Init on page load ────────────────────────────────────────────────────────
export function initI18n() {
    const lang     = getSavedLang();
    const currency = getSavedCurrency();

    applyLang(lang);
    applyCurrency(currency);
    updatePill(lang, currency);
}

// Auto-init when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initI18n);
} else {
    initI18n();
}
