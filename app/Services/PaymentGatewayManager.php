<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * PaymentGatewayManager
 *
 * Evaluates the effective display state of any registered payment gateway
 * by combining the admin's manual DB setting with automatic health checks.
 *
 * Computed states:
 *   'active'   — Gateway is visible and usable.
 *   'disabled' — Gateway is visible but non-clickable (shows offline message).
 *   'hidden'   — Gateway is completely removed from the UI.
 *
 * Priority order (highest → lowest):
 *   1. Admin sets 'hidden'           → always hidden regardless of health.
 *   2. Credentials missing/invalid   → forced to 'disabled'.
 *   3. API unreachable (cached 5min) → forced to 'disabled'.
 *   4. Admin's DB setting            → 'active' or 'disabled'.
 *
 * File: app/Services/PaymentGatewayManager.php
 */
class PaymentGatewayManager
{
    /**
     * Maps gateway slugs to their concrete service class.
     * Add future gateways here — no other file needs changing.
     *
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    protected array $drivers = [
        'bakong'       => BakongApiService::class,
        'aba_payway'   => AbaPayWayService::class,
        'aba_telegram' => AbaTelegramService::class,
        // 'stripe'    => StripeService::class,
    ];

    /**
     * Evaluate the computed display state for a gateway by slug.
     *
     * @param  string $slug  e.g. 'bakong' | 'aba_payway'
     * @return string        'active' | 'disabled' | 'hidden'
     */
    public function getComputedState(string $slug): string
    {
        $gateway = PaymentGateway::where('slug', $slug)->first();

        // 1. If the row doesn't exist or admin explicitly hid it, hide it.
        if (! $gateway || $gateway->admin_status === PaymentGateway::STATUS_HIDDEN) {
            return PaymentGateway::STATUS_HIDDEN;
        }

        // 2. Resolve the driver class for this slug.
        $driverClass = $this->drivers[$slug] ?? null;

        if (! $driverClass) {
            Log::warning("PaymentGatewayManager: no driver registered for slug [{$slug}]");
            return PaymentGateway::STATUS_HIDDEN;
        }

        /** @var PaymentGatewayInterface $driver */
        $driver = app($driverClass);

        // 3. Automatic override: credentials missing → disabled.
        if (! $driver->isConfigured()) {
            return PaymentGateway::STATUS_DISABLED;
        }

        // 4. Automatic override: API unreachable → disabled.
        //    Result is cached for 5 minutes to prevent slow page loads.
        $isReachable = Cache::remember(
            "gateway_health_{$slug}",
            300,
            fn () => $driver->isReachable()
        );

        if (! $isReachable) {
            return PaymentGateway::STATUS_DISABLED;
        }

        // 5. Return the admin's manual setting ('active' or 'disabled').
        return $gateway->admin_status;
    }

    /**
     * Return all gateways from the DB that are not 'hidden', with their
     * computed state attached. Useful for building the payment method UI.
     *
     * @return \Illuminate\Support\Collection<int, array{gateway: PaymentGateway, state: string}>
     */
    public function getVisibleGateways(): \Illuminate\Support\Collection
    {
        return PaymentGateway::orderBy('id')->get()
            ->map(function (PaymentGateway $gateway) {
                return [
                    'gateway' => $gateway,
                    'state'   => $this->getComputedState($gateway->slug),
                ];
            })
            ->filter(fn ($item) => $item['state'] !== PaymentGateway::STATUS_HIDDEN)
            ->values();
    }

    /**
     * Bust the cached health status for a specific gateway.
     * Call this after an admin manually changes a gateway's status.
     */
    public function forgetHealthCache(string $slug): void
    {
        Cache::forget("gateway_health_{$slug}");
    }
}
