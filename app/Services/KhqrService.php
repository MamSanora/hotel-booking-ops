<?php

namespace App\Services;

use App\Models\Booking;

/**
 * KhqrService
 *
 * Generates EMVCo-compliant KHQR strings for the Bakong payment network.
 *
 * This implements the "Solo/Individual Merchant" approach (Tag 29) which
 * only requires a personal Bakong Account ID — no merchant registration,
 * business license, or MoA needed.
 *
 * The generated string conforms to the KHQR specification published by
 * the National Bank of Cambodia (NBC). Any KHQR-compatible banking app
 * (ABA, Wing, ACLEDA, etc.) can parse and process payments from this QR.
 *
 * Tag structure (EMVCo QR Code Specification):
 *   00 — Payload Format Indicator (always "01")
 *   01 — Point of Initiation Method ("12" = Dynamic, amount is embedded)
 *   29 — Merchant Account Info — Bakong Individual
 *        └─ 00 Globally Unique Identifier
 *        └─ 01 Bakong Account ID  (e.g. "012345678@aba")
 *        └─ 02 Merchant Name
 *        └─ 03 Merchant City
 *   52 — Merchant Category Code ("5999" — misc retail)
 *   53 — Transaction Currency ("840" = USD, "116" = KHR)
 *   54 — Transaction Amount
 *   58 — Country Code ("KH")
 *   59 — Merchant Name
 *   60 — Merchant City
 *   62 — Additional Data Field Template
 *        └─ 01 Bill Number (our booking reference)
 *   63 — CRC (4-char CRC16/CCITT checksum — always last)
 *
 * File: app/Services/KhqrService.php
 */
class KhqrService
{
    /**
     * Bakong individual account GUID.
     * Per KHQR spec, the GUID for Bakong individual (Tag 29) must always
     * be the fixed string "bakong.net.kh".
     */
    protected const BAKONG_GUID = 'bakong.net.kh';

    protected string $bakongAccountId;
    protected string $merchantName;
    protected string $merchantCity;
    protected string $currency;

    public function __construct()
    {
        $this->bakongAccountId = config('bakong.account_id', '');
        $this->merchantName    = config('bakong.merchant_name', 'Hotel Sarana');
        $this->merchantCity    = config('bakong.merchant_city', 'Phnom Penh');
        $this->currency        = config('bakong.currency', '840'); // '840'=USD, '116'=KHR
    }

    // ── Public API ─────────────────────────────────────────────────────────

    /**
     * Generate a complete KHQR data package for a booking.
     *
     * @param  Booking $booking
     * @return array{
     *     khqr_string: string,
     *     md5_hash:    string,
     * }
     */
    public function generate(Booking $booking, ?float $amount = null): array
    {
        $paymentAmount   = $amount ?? $booking->total_price;
        $formattedAmount = number_format((float) $paymentAmount, 2, '.', '');
        $ref             = $booking->referenceNumber();

        // The library's generateIndividual() is STATIC and takes only IndividualInfo.
        // Amount, currency (as int), and bill number MUST be set on IndividualInfo.
        $individualInfo = new \KHQR\Models\IndividualInfo(
            $this->bakongAccountId,
            $this->merchantName,
            $this->merchantCity,
            null,                               // acquiringBank
            null,                               // accountInformation
            \KHQR\Helpers\KHQRData::CURRENCY_USD, // currency as int 840
            (float) $formattedAmount,           // amount
            $ref,                               // billNumber = booking reference
        );

        $response = \KHQR\BakongKHQR::generateIndividual($individualInfo);

        return [
            'khqr_string' => $response->data['qr'],
            'md5_hash'    => $response->data['md5'],
        ];
    }

    /**
     * Format a Tag-Length-Value (TLV) field.
     * Length is zero-padded to 2 digits.
     */
    protected function tlv(string $tag, string $value): string
    {
        $len = strlen($value);
        return $tag . str_pad((string) $len, 2, '0', STR_PAD_LEFT) . $value;
    }

    /**
     * CRC16-CCITT (0xFFFF initial, 0x1021 polynomial).
     *
     * This is the algorithm specified in the EMVCo QR Code Specification
     * and mandated by the KHQR standard.
     *
     * @param  string $data  The full KHQR string up to and including "6304"
     * @return int           The 16-bit CRC value
     */
    protected function crc16(string $data): int
    {
        $crc = 0xFFFF;

        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= (ord($data[$i]) << 8);

            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return $crc;
    }
}
