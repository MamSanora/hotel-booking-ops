<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use chillerlan\QRCode\QRCode;

class KhqrValidatorService
{
    /**
     * Reads a QR code image and validates that it does not contain a specific amount (Tag 54).
     *
     * @param string $imagePath
     * @return bool True if valid (amount is $0 or missing), false otherwise.
     * @throws Exception If QR cannot be read or is invalid format.
     */
    public function validateRefundQr(string $imagePath): bool
    {
        try {
            $qr = new QRCode();
            $result = $qr->readFromFile($imagePath);
            $khqrString = (string) $result; // In php-qrcode, casting result to string gets the content
            
            if (empty($khqrString)) {
                throw new Exception("QR code contains no data.");
            }

            return $this->isValidZeroAmountKhqr($khqrString);
        } catch (\Throwable $e) {
            Log::error('QR Validation failed', ['error' => $e->getMessage(), 'path' => $imagePath]);
            throw new Exception("Could not read QR code. Please ensure the image is clear and contains a valid KHQR code.");
        }
    }

    /**
     * Parses the TLV string and checks if Tag 54 (Transaction Amount) is missing or 0.
     */
    protected function isValidZeroAmountKhqr(string $payload): bool
    {
        $tags = $this->parseTlv($payload);
        
        // If Tag 54 exists and is > 0, reject it.
        if (isset($tags['54']) && (float) $tags['54'] > 0) {
            return false;
        }

        // Check if Tag 29 or Tag 30 (Merchant Account Info) exists to ensure it's a KHQR
        if (!isset($tags['29']) && !isset($tags['30'])) {
            throw new Exception("Not a valid KHQR code (Missing Merchant Account Info).");
        }

        return true;
    }

    /**
     * Parses a TLV (Tag-Length-Value) string into an associative array.
     */
    protected function parseTlv(string $data): array
    {
        $tags = [];
        $i = 0;
        $len = strlen($data);

        while ($i < $len) {
            if ($i + 4 > $len) break;

            $tag = substr($data, $i, 2);
            $lengthStr = substr($data, $i + 2, 2);
            $length = (int) $lengthStr;
            
            $i += 4;
            
            if ($i + $length > $len) break;

            $value = substr($data, $i, $length);
            $tags[$tag] = $value;
            
            $i += $length;
        }

        return $tags;
    }
}
