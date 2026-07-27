<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * KhqrGenerator
 *
 * Generates an ABA-flavoured KHQR (EMVCo Merchant-Presented QR) string
 * entirely offline \u2014 no API call, no pre-uploaded images required.
 *
 * The payload format was reverse-engineered from the hotel\u2019s own ABA QR
 * codes and matches ABA Bank Cambodia\u2019s Tag-30 merchant-info structure.
 *
 * Usage:
 *   $qr = KhqrGenerator::forAmount(30.00);
 *
 * The returned string can be passed directly to any QR renderer
 * (qrcodejs, qrcode.js, etc.).
 */
class KhqrGenerator
{
    /**
     * Convenience factory: reads merchant credentials from config and
     * generates a KHQR string for the given USD amount.
     *
     * @param  float  $amount  The exact amount to encode in the QR (e.g. 6.00).
     * @return string          The complete EMVCo KHQR payload string.
     */
    public static function forAmount(float $amount): string
    {
        $merchantName  = config('telegram.aba_merchant_name', 'KEO SAMNANG DARAMEAS');
        $accountNumber = config('telegram.aba_account_number', '');

        if (empty($accountNumber)) {
            Log::warning('KhqrGenerator: TELEGRAM_ABA_ACCOUNT_NUMBER is not set in .env');
        }

        return self::generate($merchantName, $accountNumber, $amount);
    }

    /**
     * Build the raw KHQR EMVCo TLV payload string.
     *
     * @param  string  $merchantName   Merchant name (max 25 chars, uppercase).
     * @param  string  $accountNumber  ABA account number (e.g. 126072520000678).
     * @param  float   $amount         Transaction amount in USD.
     * @param  string  $currency       ISO 4217 numeric code. 840 = USD, 116 = KHR.
     * @return string
     */
    public static function generate(
        string $merchantName,
        string $accountNumber,
        float  $amount,
        string $currency = '840'
    ): string {
        // Tag 00: Payload Format Indicator
        $payload  = self::tlv('00', '01');
        // Tag 01: Point-of-Initiation Method (12 = dynamic, one-time use)
        $payload .= self::tlv('01', '12');

        // Tag 30: ABA Merchant Account Information
        //   Sub-tag 00: Globally Unique Identifier (ABA\u2019s acquirer BIC / domain)
        //   Sub-tag 01: Merchant account number
        //   Sub-tag 02: Acquiring bank name
        $sub30  = self::tlv('00', 'abaakhppxxx@abaa');
        $sub30 .= self::tlv('01', $accountNumber);
        $sub30 .= self::tlv('02', 'ABA Bank');
        $payload .= self::tlv('30', $sub30);

        // Tag 52: Merchant Category Code (4814 = Telecommunications / Hotels)
        $payload .= self::tlv('52', '4814');
        // Tag 53: Transaction Currency
        $payload .= self::tlv('53', $currency);
        // Tag 54: Transaction Amount (no trailing zeros stripped \u2014 must be fixed 2 d.p.)
        $payload .= self::tlv('54', number_format($amount, 2, '.', ''));
        // Tag 58: Country Code
        $payload .= self::tlv('58', 'KH');
        // Tag 59: Merchant Name (trim to 25 chars, ABA enforces this silently)
        $payload .= self::tlv('59', mb_substr($merchantName, 0, 25));
        // Tag 60: Merchant City
        $payload .= self::tlv('60', 'PHNOM PENH');

        // Tag 62: Additional Data Field Template
        //   Sub-tag 01: Bill Number / Invoice Reference
        $sub62  = self::tlv('01', 'INV-' . time());
        $payload .= self::tlv('62', $sub62);

        // Tag 63: CRC-16/CCITT-FALSE checksum (4 hex characters, appended last)
        $payload .= '6304';
        $payload .= self::crc16($payload);

        return $payload;
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Encode a single EMVCo TLV (Tag-Length-Value) triplet.
     * Length is always zero-padded to exactly 2 digits.
     */
    private static function tlv(string $tag, string $value): string
    {
        return $tag . str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT) . $value;
    }

    /**
     * Calculate the CRC-16/CCITT-FALSE (poly 0x1021, init 0xFFFF, no reflect)
     * checksum and return it as a 4-character uppercase hex string.
     *
     * This is the exact algorithm mandated by the EMVCo QR Code Specification
     * and used by the National Bank of Cambodia for KHQR validation.
     */
    private static function crc16(string $data): string
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000)
                    ? (($crc << 1) ^ 0x1021) & 0xFFFF
                    : ($crc << 1) & 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
