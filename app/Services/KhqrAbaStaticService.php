<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;

/**
 * KhqrAbaStaticService
 *
 * Gateway service for the "KHQR and ABA Pay" payment method.
 *
 * Unlike the Bakong KHQR gateway (which generates a dynamic QR per transaction
 * via the Bakong Open API), this gateway uses STATIC pre-screenshotted QR code
 * images saved to public/qr_codes/hotel_owner_QR_codes/.
 *
 * The image filename convention is: qr_{amount}.png
 *   e.g. qr_30.00.png  → $30.00 USD
 *        qr_6.00.png   → $6.00 USD (20% of $30)
 *
 * Because this gateway needs no external API, isConfigured() and isReachable()
 * always return true, allowing PaymentGatewayManager to mark it as 'active'
 * without any health-check delay.
 *
 * The payment confirmation flow is manual:
 *   1. Guest scans the static QR code and transfers the amount.
 *   2. Receptionist verifies the transfer notification from the hotel owner's
 *      ABA/Bakong app and manually confirms the booking via the Reception dashboard.
 *
 * No auto-polling. No Bakong md5_hash. No ABA callback.
 */
class KhqrAbaStaticService implements PaymentGatewayInterface
{
    /**
     * Always configured — no credentials required (static images, no API).
     */
    public function isConfigured(): bool
    {
        return true;
    }

    /**
     * Always reachable — no network call needed (static local files).
     */
    public function isReachable(): bool
    {
        return true;
    }

    /**
     * Build the public path to the QR code image for a given amount.
     *
     * @param  float  $amount  The amount to charge (e.g. 30.00, 6.00).
     * @return string          Public asset path for use in <img src="...">
     */
    public function getQrImagePath(float $amount): string
    {
        return asset('qr_codes/hotel_owner_QR_codes/qr_' . number_format($amount, 2) . '.png');
    }

    /**
     * Check if the QR image file physically exists on disk for a given amount.
     * Used by the Admin QR Calculator to flag missing images.
     *
     * @param  float  $amount
     * @return bool
     */
    public function qrImageExists(float $amount): bool
    {
        $filename = 'qr_' . number_format($amount, 2) . '.png';
        return file_exists(public_path('qr_codes/hotel_owner_QR_codes/' . $filename));
    }
}
