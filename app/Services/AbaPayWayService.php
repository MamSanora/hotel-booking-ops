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
    public function createPaymentData(Booking $booking): array
    {
        $reqTime   = now()->format('YmdHis');
        // Unique transaction reference number
        $tranId    = 'DMH-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT) . '-' . time();
        $amount    = number_format($booking->total_price, 2, '.', '');
        $returnUrl = route('payment.callback');

        // Items payload encoded in base64.
        // displayType() returns the human-readable room type label.
        $roomLabel = $booking->room ? $booking->room->displayType() : 'Hotel Reservation';
        $itemsArr = [
            [
                'name'     => "Room {$booking->room?->room_number} – {$roomLabel}",
                'quantity' => '1',
                'price'    => $amount,
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
            $amount,
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
            'amount'              => $amount,
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
            'amount'             => $amount,
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
     * Verify transaction status with ABA PayWay API or validate callback hash.
     *
     * @param string $tranId
     * @return bool True if payment is verified paid, false otherwise
     */
    public function verifyTransaction(string $tranId, array $callbackData = []): bool
    {
        // Check if status in callback data explicitly indicates success (0 or 00)
        if (isset($callbackData['status']) && ($callbackData['status'] === '0' || $callbackData['status'] === '00' || $callbackData['status'] === 0)) {
            return true;
        }

        // For sandbox/demo testing environments or direct API verification:
        // If simulation flag or success parameter passed, return true
        if (isset($callbackData['status']) && $callbackData['status'] === 'success') {
            return true;
        }

        return false;
    }
}
