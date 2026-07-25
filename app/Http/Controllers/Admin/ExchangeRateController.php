<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * ExchangeRateController
 *
 * Handles the manual "Sync Rate Now" action triggered from the admin dashboard.
 *
 * Rate limiting:
 *   One sync per admin per 30 minutes, enforced via cache (same pattern as
 *   BackupController). Stores the Unix expiry timestamp as the cache value so
 *   the countdown can be displayed in the UI.
 *
 * Route: POST /admin/exchange-rate/sync  → admin.exchange-rate.sync
 */
class ExchangeRateController extends Controller
{
    /** 30-minute cooldown between manual syncs per admin. */
    private const COOLDOWN_SECONDS = 1_800;

    public function sync(ExchangeRateService $service): RedirectResponse
    {
        $adminId  = Auth::guard('admin')->id();
        $cacheKey = "admin_exchange_rate_sync_{$adminId}";

        // ── Rate-limit check ───────────────────────────────────────────────
        if (Cache::has($cacheKey)) {
            $expiresAt   = Cache::get($cacheKey, now()->timestamp);
            $secondsLeft = max(0, $expiresAt - now()->timestamp);
            $minutesLeft = (int) ceil($secondsLeft / 60);

            return redirect()
                ->route('admin.dashboard')
                ->with('exchange_rate_error', "Rate already synced recently. Please wait {$minutesLeft} more minute(s).");
        }

        // ── Fetch & Save ───────────────────────────────────────────────────
        try {
            $record = $service->fetchAndSave();

            // Set the cooldown lock (stores expiry timestamp so the blade
            // can compute the remaining seconds without TTL introspection).
            Cache::put(
                $cacheKey,
                now()->addSeconds(self::COOLDOWN_SECONDS)->timestamp,
                self::COOLDOWN_SECONDS
            );

            return redirect()
                ->route('admin.dashboard')
                ->with('exchange_rate_success', sprintf(
                    'Exchange rate synced: 1 USD = %s KHR (dated %s)',
                    number_format($record->rate, 2),
                    $record->fetched_at->format('M d, Y')
                ));

        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('exchange_rate_error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
