<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AbaPayWayService
 *
 * Encapsulates integration logic for ABA PayWay Payment Gateway.
 * Generates secure cryptographic hashes, formats payment parameters,
 * and handles transaction verification with ABA servers.
 *
 * File: app/Services/AbaPayWayService.php
 */
class AbaPayWayService implements PaymentGatewayInterface
{
    protected string $merchantId;
    protected string $apiKey;
    protected string $apiUrl;
    protected string $currency;

    public function __construct()
    {
        $this->merchantId = config('payway.merchant_id', 'demo_merchant');
        $this->apiKey     = config('payway.api_key', 'demo_api_key');
        $this->apiUrl     = config('payway.api_url', 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase');
        $this->currency   = config('payway.currency', 'USD');
    }

    // ── PaymentGatewayInterface ────────────────────────────────────────────

    /**
     * Returns true if real credentials are set (not the 'demo_' placeholders).
     */
    public function isConfigured(): bool
    {
        $merchantId = config('payway.merchant_id', '');
        $apiKey     = config('payway.api_key', '');

        return ! empty($merchantId)
            && ! empty($apiKey)
            && $merchantId !== 'demo_merchant'
            && $apiKey     !== 'demo_api_key';
    }

    /**
     * Returns true if the ABA PayWay sandbox/production URL is reachable.
     */
    public function isReachable(): bool
    {
        try {
            // A lightweight GET to the base checkout domain (no auth needed for a ping)
            $baseUrl = 'https://checkout-sandbox.payway.com.kh';
            $response = Http::timeout(5)->get($baseUrl);

            // Any HTTP response (even 4xx) means the server is up
            return $response->status() > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Generate HMAC SHA512 hash signature required by ABA PayWay.
     *
     * Full v3 Purchase field concatenation order (from ABA Developer Suite):
     * req_time + merchant_id + tran_id + amount + items + shipping +
     * ctid + pwt + firstname + lastname + email + phone + type +
     * payment_option + return_url + cancel_url + continue_success_url +
     * return_deeplink + currency + custom_fields + return_params
     */
    public function generateHash(
        string $reqTime,
        string $tranId,
        string $amount,
        string $items = '',
        string $shipping = '0',
        string $type = 'purchase',
        string $continueSuccessUrl = '',
        string $returnUrl = '',
        string $cancelUrl = ''
    ): string {
        // All optional fields that must still be present as empty strings
        $ctid          = '';
        $pwt           = '';
        $firstname     = '';
        $lastname      = '';
        $email         = '';
        $phone         = '';
        $paymentOption = '';
        $returnDeeplink = '';
        $customFields  = '';
        $returnParams  = '';

        $str = $reqTime
             . $this->merchantId
             . $tranId
             . $amount
             . $items
             . $shipping
             . $ctid
             . $pwt
             . $firstname
             . $lastname
             . $email
             . $phone
             . $type
             . $paymentOption
             . $returnUrl
             . $cancelUrl
             . $continueSuccessUrl
             . $returnDeeplink
             . $this->currency
             . $customFields
             . $returnParams;

        return base64_encode(hash_hmac('sha512', $str, $this->apiKey, true));
    }

    /**
     * Create checkout payload and return QR Code / Payment Link details for a booking.
     *
     * @param Booking $booking
     * @return array Contains tran_id, amount, qr_code_url, payment_link, and form payload
     */
    public function createPaymentData(Booking $booking, ?float $amount = null): array
    {
        $reqTime   = now()->format('YmdHis');
        // Unique transaction reference number
        $tranId    = 'DMH-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT) . '-' . time();
        
        $paymentAmount = $amount ?? $booking->total_price;
        $formattedAmount    = number_format($paymentAmount, 2, '.', '');
        $returnUrl = route('payment.callback');

        // Items payload encoded in base64.
        // displayType() returns the human-readable room type label.
        $roomLabel = $booking->room ? $booking->room->displayType() : 'Hotel Reservation';
        $itemsArr = [
            [
                'name'     => "Room {$booking->room?->room_number} – {$roomLabel}",
                'quantity' => '1',
                'price'    => $formattedAmount,
            ],
        ];
        $items = base64_encode(json_encode($itemsArr));

        $shipping           = '0';
        $type               = 'purchase';
        $continueSuccessUrl = route('payment.success', $booking->id);
        $cancelUrl          = route('payment.show', $booking->id);

        // Generate security signature using full v3 field concatenation
        $hash = $this->generateHash(
            $reqTime,
            $tranId,
            $formattedAmount,
            $items,
            $shipping,
            $type,
            $continueSuccessUrl,
            $returnUrl,
            $cancelUrl
        );

        // Build the full POST payload for ABA PayWay
        $payload = [
            'merchant_id'         => $this->merchantId,
            'tran_id'             => $tranId,
            'amount'              => $formattedAmount,
            'items'               => $items,
            'shipping'            => $shipping,
            'ctid'                => '',
            'pwt'                 => '',
            'firstname'           => '',
            'lastname'            => '',
            'email'               => '',
            'phone'               => '',
            'type'                => $type,
            'payment_option'      => '',
            'return_url'          => $returnUrl,
            'cancel_url'          => $cancelUrl,
            'continue_success_url'=> $continueSuccessUrl,
            'return_deeplink'     => '',
            'currency'            => $this->currency,
            'custom_fields'       => '',
            'return_params'       => '',
            'req_time'            => $reqTime,
            'hash'                => $hash,
        ];

        // POST server-side to the ABA PayWay API
        $response = Http::asForm()->post($this->apiUrl, $payload);
        $json     = $response->json();

        $success = isset($json['status']['code']) && (string) $json['status']['code'] === '00';

        return [
            'merchant_id'        => $this->merchantId,
            'transaction_id'     => $tranId,
            'merchant_reference' => $booking->referenceNumber(),
            'amount'             => $formattedAmount,
            'currency'           => $this->currency,
            'req_time'           => $reqTime,
            'hash'               => $hash,
            'items'              => $items,
            'return_url'         => $returnUrl,
            'api_success'        => $success,
            'api_error'          => $success ? null : ($json['status']['message'] ?? 'Unknown error from ABA PayWay'),
            'qr_string'          => $json['qrString'] ?? null,
            'qr_image'           => $json['qrImage'] ?? null,          // base64 data URI
            'abapay_deeplink'    => $json['abapay_deeplink'] ?? null,
            'app_store'          => $json['app_store'] ?? null,
            'play_store'         => $json['play_store'] ?? null,
        ];
    }

    /**
     * Build the signed payload for ABA PayWay Hosted Checkout (browser-redirect flow).
     *
     * Unlike createPaymentData(), this method makes NO server-side HTTP call.
     * The caller renders a hidden HTML form in the browser that auto-submits
     * this data directly to ABA's checkout page, where ABA shows their own
     * payment UI (including Simulate Success/Failure buttons in sandbox).
     *
     * @param  Booking    $booking
     * @param  float|null $amount  Amount to charge; defaults to booking total.
     * @return array{
     *   merchant_id: string,
     *   transaction_id: string,
     *   amount: string,
     *   items: string,
     *   currency: string,
     *   req_time: string,
     *   hash: string,
     *   return_url: string,
     *   cancel_url: string,
     *   continue_success_url: string,
     * }
     */
    public function buildHostedCheckoutData(Booking $booking, ?float $amount = null): array
    {
        $reqTime = now()->format('YmdHis');
        // Unique transaction reference stored on the Transaction record so the callback can match it.
        $tranId  = 'DMH-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT) . '-' . time();

        $paymentAmount   = $amount ?? $booking->total_price;
        $formattedAmount = number_format($paymentAmount, 2, '.', '');

        // Callback URL — ABA redirects the guest's browser here after payment.
        // Also receives server-to-server webhook.
        $returnUrl          = route('payment.callback');
        $continueSuccessUrl = route('payment.success', $booking->id);
        $cancelUrl          = route('payment.show', $booking->id);

        $roomLabel = $booking->room ? $booking->room->displayType() : 'Hotel Reservation';
        $itemsArr  = [
            [
                'name'     => "Room {$booking->room?->room_number} – {$roomLabel}",
                'quantity' => '1',
                'price'    => $formattedAmount,
            ],
        ];
        $items    = base64_encode(json_encode($itemsArr));
        $shipping = '0';
        $type     = 'purchase';

        $hash = $this->generateHash(
            $reqTime,
            $tranId,
            $formattedAmount,
            $items,
            $shipping,
            $type,
            $continueSuccessUrl,
            $returnUrl,
            $cancelUrl
        );

        return [
            'merchant_id'         => $this->merchantId,
            'transaction_id'      => $tranId,
            'amount'              => $formattedAmount,
            'items'               => $items,
            'shipping'            => $shipping,
            'type'                => $type,
            'currency'            => $this->currency,
            'req_time'            => $reqTime,
            'hash'                => $hash,
            'return_url'          => $returnUrl,
            'cancel_url'          => $cancelUrl,
            'continue_success_url'=> $continueSuccessUrl,
        ];
    }

    /**
     * Verify an ABA PayWay callback using the HMAC-SHA512 header signature.
     *
     * ABA sends an `X-PayWay-HMAC-SHA512` header with every POST callback.
     * We re-hash the raw request body with our API key and compare. If the
     * signatures match, the request is genuinely from ABA and the status can
     * be trusted.
     *
     * Falls back to a status-only check when the header is absent (e.g. local
     * Postman tests, or ABA's browser GET redirect after payment).
     *
     * @param string $tranId        The ABA tran_id we stored on our Transaction.
     * @param array  $callbackData  Parsed request parameters from ABA.
     * @param string $rawBody       The raw POST body string (for HMAC comparison).
     * @param string $headerHash    The value of X-PayWay-HMAC-SHA512 header (empty string if absent).
     * @return bool  True if payment is verified as successful; false otherwise.
     */
    public function verifyTransaction(
        string $tranId,
        array  $callbackData = [],
        string $rawBody = '',
        string $headerHash = ''
    ): bool {
        // ── 1. HMAC Header Verification (most secure — used for POST webhooks) ──
        if ($headerHash !== '' && $rawBody !== '') {
            $expectedHash = base64_encode(
                hash_hmac('sha512', $rawBody, $this->apiKey, true)
            );

            if (! hash_equals($expectedHash, $headerHash)) {
                Log::warning('ABA PayWay: HMAC signature mismatch on callback', [
                    'tran_id' => $tranId,
                ]);
                return false;
            }

            // Hash is valid — now check the status code in the payload.
            $status = $callbackData['status'] ?? null;
            return $status === '0' || $status === '00' || $status === 0;
        }

        // ── 2. Status-only fallback (ABA browser redirect GET / local testing) ──
        // When ABA redirects the browser back to return_url, it uses GET with
        // query params (no body, no HMAC header). Trust the status field.
        $status = $callbackData['status'] ?? null;
        if ($status === '0' || $status === '00' || $status === 0 || $status === 'success') {
            return true;
        }

        return false;
    }
}
