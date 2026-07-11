<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BakongApiService
 *
 * Communicates with the Bakong Open API to verify KHQR payment status.
 *
 * Strategy: After a KHQR is generated and shown to the guest, the frontend
 * polls our server every few seconds. On each poll, this service queries
 * the Bakong Open API using the MD5 hash of the KHQR string. If the API
 * returns a success status, the booking is confirmed.
 *
 * Authentication: The Bakong Open API uses Bearer token authentication.
 * Register your email at the Bakong developer portal to receive a token.
 *
 * Key endpoint:
 *   POST /v1/check_transaction_by_md5
 *   Body: { "md5": "<md5_hash_of_khqr_string>" }
 *   Response: { "responseCode": 0, "data": { "hash": "...", "fromAccountId": "...", ... } }
 *
 * File: app/Services/BakongApiService.php
 */
class BakongApiService implements PaymentGatewayInterface
{
    protected string $apiUrl;
    protected string $apiToken;

    /** Bakong API response codes that indicate a completed payment */
    protected const SUCCESS_CODE = 0;

    /** Known Bakong API tracking statuses that mean money has arrived */
    protected const SUCCESS_STATUSES = [
        'RECEIVE_AT_RECEIVER_BANK',
        'COMPLETED',
    ];

    public function __construct()
    {
        $this->apiUrl   = rtrim(config('bakong.api_url', 'https://api-bakong.nbc.gov.kh'), '/');
        $this->apiToken = config('bakong.api_token', '');
    }

    // ── PaymentGatewayInterface ────────────────────────────────────────────

    /**
     * Returns true if the Bakong account ID and API token are configured.
     */
    public function isConfigured(): bool
    {
        return ! empty(config('bakong.account_id'))
            && ! empty(config('bakong.api_token'));
    }

    // ── Public API ─────────────────────────────────────────────────────────

    /**
     * Check whether a KHQR transaction has been paid.
     *
     * Queries Bakong Open API using the MD5 hash stored on the transaction.
     * Returns true if the payment is confirmed, false otherwise.
     *
     * Also updates the transaction's tracking_status with the latest value
     * from Bakong for auditing purposes.
     *
     * @param  Transaction $transaction
     * @return bool
     */
    public function checkPayment(Transaction $transaction): bool
    {
        if (empty($transaction->md5_hash)) {
            Log::warning('BakongApiService: transaction has no md5_hash', [
                'transaction_id' => $transaction->id,
            ]);
            return false;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->timeout(10)
                ->post("{$this->apiUrl}/v1/check_transaction_by_md5", [
                    'md5' => $transaction->md5_hash,
                ]);

            $body = $response->json();

            Log::info('BakongApiService: checkPayment response', [
                'transaction_id' => $transaction->id,
                'md5_hash'       => $transaction->md5_hash,
                'status_code'    => $response->status(),
                'body'           => $body,
            ]);

            // Non-2xx responses mean API failure — do not confirm payment.
            if (! $response->successful()) {
                return false;
            }

            // Extract tracking status from response (may vary by API version)
            $trackingStatus = $body['data']['trackingStatus']
                ?? $body['data']['status']
                ?? null;

            // Persist the latest tracking status for audit trail
            if ($trackingStatus && $trackingStatus !== $transaction->tracking_status) {
                $transaction->update(['tracking_status' => $trackingStatus]);
            }

            // Response code 0 = success per Bakong API specification
            $responseCode = $body['responseCode'] ?? -1;

            if ($responseCode === self::SUCCESS_CODE) {
                return in_array($trackingStatus, self::SUCCESS_STATUSES, true);
            }

            return false;

        } catch (\Throwable $e) {
            Log::error('BakongApiService: exception during checkPayment', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verify the Bakong API is reachable and the token is valid.
     * Useful for health-check endpoints or admin diagnostics.
     *
     * @return bool
     */
    public function isReachable(): bool
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->timeout(5)
                ->get("{$this->apiUrl}/v1/me");

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
