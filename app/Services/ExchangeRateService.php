<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ExchangeRateService
 *
 * Manages fetching, storing, and reading the USD→KHR exchange rate.
 *
 * DATA FLOW:
 *   External API (exchangerate.fun) → database (exchange_rates) → cache
 *
 * READ FLOW (fastest-first):
 *   cache → database latest row → hardcoded fallback (4,100 KHR)
 *
 * WHY exchangerate.fun?
 *   A free, no-API-key-required currency aggregator supporting 170+ currencies
 *   including KHR (Cambodian Riel). Hourly updates from multiple global sources.
 *   Endpoint: https://api.exchangerate.fun/latest?base=USD
 *   Response: { "base": "USD", "rates": { "KHR": 4045.0, ... } }
 *
 * WHY A FALLBACK?
 *   During a live demo or when the server has no internet, the app must
 *   never crash or show a broken UI. The three-level fallback guarantees
 *   a value is always available.
 */
class ExchangeRateService
{
    /** Cache key for the current USD→KHR rate */
    private const CACHE_KEY = 'exchange_rate_usd_khr';

    /** Cache TTL: 23 hours (rate is synced daily at 08:00) */
    private const CACHE_TTL_SECONDS = 23 * 3600;

    /**
     * Hardcoded fallback rate (approximate NBC mid-rate as of project date).
     * Used when both the cache and the database are empty.
     */
    private const FALLBACK_RATE = 4_100.0;

    // ── Public API ──────────────────────────────────────────────────────────

    /**
     * Orchestrates fetching the USD→KHR rate.
     * Tries the primary NBC web scraper first. If it fails, logs a warning
     * and falls back to the API.
     *
     * @return ExchangeRate
     */
    public function fetchAndSave(): ExchangeRate
    {
        try {
            $record = $this->fetchFromNBC();
        } catch (\Throwable $e) {
            Log::warning("NBC Website Scraper Failed: {$e->getMessage()}. Falling back to exchangerate.fun API.");
            $record = $this->fetchFromAPI();
        }

        // Refresh cache immediately so the next read is instant.
        Cache::put(self::CACHE_KEY, (float) $record->rate, self::CACHE_TTL_SECONDS);

        return $record;
    }

    /**
     * Primary Source: Scrape the official NBC website.
     *
     * @return ExchangeRate
     * @throws \RuntimeException
     */
    private function fetchFromNBC(): ExchangeRate
    {
        // NBC firewall blocks requests without a User-Agent. We impersonate Chrome.
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
            ->get('https://www.nbc.gov.kh/english/economic_research/exchange_rate.php');

        if (! $response->successful()) {
            throw new \RuntimeException("NBC HTTP returned status {$response->status()}");
        }

        $html = $response->body();

        // The rate is injected in HTML as: Official Exchange Rate : <font color="#FF3300">4,041</font> KHR / USD
        if (preg_match('/Official Exchange Rate\s*:\s*<font[^>]*>([\d,]+)<\/font>/i', $html, $matches)) {
            $rateString = str_replace(',', '', $matches[1]);
            $rate = (float) $rateString;

            $record = ExchangeRate::create([
                'base'       => 'USD',
                'target'     => 'KHR',
                'rate'       => $rate,
                'source'     => 'nbc_website',
                'fetched_at' => now(),
            ]);

            Log::info("ExchangeRateService: synced USD→KHR = {$rate} (source: nbc_website)");

            return $record;
        }

        throw new \RuntimeException("Could not find exchange rate pattern in NBC HTML.");
    }

    /**
     * Secondary Source (Fallback): exchangerate.fun API.
     *
     * @return ExchangeRate
     * @throws \RuntimeException
     */
    private function fetchFromAPI(): ExchangeRate
    {
        $response = Http::timeout(10)
            ->get('https://api.exchangerate.fun/latest', [
                'base' => 'USD',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Exchange rate API returned HTTP {$response->status()}");
        }

        $data = $response->json();

        if (empty($data['rates']['KHR'])) {
            throw new \RuntimeException("API response did not contain a KHR rate.");
        }

        $rate = (float) $data['rates']['KHR'];

        $fetchedAt = isset($data['timestamp'])
            ? \Carbon\Carbon::createFromTimestamp($data['timestamp'])
            : now();

        $record = ExchangeRate::create([
            'base'       => 'USD',
            'target'     => 'KHR',
            'rate'       => $rate,
            'source'     => 'exchangerate_fun',
            'fetched_at' => $fetchedAt,
        ]);

        Log::info("ExchangeRateService: synced USD→KHR = {$rate} (source: exchangerate_fun)");

        return $record;
    }

    /**
     * Return the current USD→KHR rate using the fastest available source.
     *
     * Priority:
     *   1. Laravel Cache (in-memory / file / Redis — instant)
     *   2. Latest database row (survives cache flushes)
     *   3. Hardcoded constant (last resort — never fails)
     */
    public function current(): float
    {
        // 1. Cache hit
        if (Cache::has(self::CACHE_KEY)) {
            return (float) Cache::get(self::CACHE_KEY);
        }

        // 2. Database
        $latest = ExchangeRate::usdToKhr()->first();
        if ($latest) {
            // Re-populate the cache so next call is instant.
            Cache::put(self::CACHE_KEY, $latest->rate, self::CACHE_TTL_SECONDS);
            return $latest->rate;
        }

        // 3. Hardcoded fallback
        Log::warning('ExchangeRateService: no rate in cache or DB, using hardcoded fallback.');
        return self::FALLBACK_RATE;
    }

    /**
     * Return the latest ExchangeRate Eloquent model, or null if none exists yet.
     */
    public function lastSynced(): ?ExchangeRate
    {
        return ExchangeRate::usdToKhr()->first();
    }

    /**
     * Return recent sync history (for the admin panel history log).
     *
     * @param int $limit  Number of records to return (default: 10)
     */
    public function recentHistory(int $limit = 10)
    {
        return ExchangeRate::usdToKhr()->limit($limit)->get();
    }
}
