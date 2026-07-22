<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AbaTelegramService
 *
 * Implements the ABA Telegram payment gateway.
 *
 * Strategy:
 *   1. The guest is shown a static transfer instruction page with the hotel's
 *      ABA account number and their booking reference as remark.
 *   2. The guest transfers money via ABA Mobile / Internet Banking.
 *   3. ABA sends a transaction notification to the hotel owner's Telegram.
 *   4. The hotel owner forwards the notification into a private Telegram group
 *      where our custom bot lives.
 *   5. Telegram POSTs the forwarded message to our webhook.
 *   6. This service's webhook handler (TelegramWebhookController) parses the
 *      message, extracts the booking reference, and marks the transaction paid.
 *
 * Health check:
 *   isConfigured() ? TELEGRAM_BOT_TOKEN is set in .env
 *   isReachable()  ? Telegram Bot API /getMe responds with ok:true
 *
 * File: app/Services/AbaTelegramService.php
 */
class AbaTelegramService implements PaymentGatewayInterface
{
    /** Base URL for the Telegram Bot API. */
    protected const TELEGRAM_API = 'https://api.telegram.org';

    protected string $botToken;
    protected string $abaAccountNumber;
    protected string $groupChatId;

    public function __construct()
    {
        $this->botToken         = config('telegram.bot_token', '');
        $this->abaAccountNumber = config('telegram.aba_account_number', '');
        $this->groupChatId      = config('telegram.group_chat_id', '');
    }

    // -- PaymentGatewayInterface --------------------------------------------

    /**
     * Returns true if a bot token has been configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->botToken);
    }

    /**
     * Pings the Telegram Bot API (/getMe) to verify the bot token is valid
     * and the API is reachable. Uses a 5-second timeout.
     */
    public function isReachable(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->get(self::TELEGRAM_API . "/bot{$this->botToken}/getMe");

            return $response->successful() && ($response->json('ok') === true);
        } catch (\Throwable) {
            return false;
        }
    }

    // -- Payment Verification -----------------------------------------------

    /**
     * Parse a Telegram message text (forwarded from ABA bot) and attempt to
     * match it against a pending transaction by the booking reference embedded
     * in the "Remark" field.
     *
     * ABA notification messages generally look like:
     *   "You received $25.00 from John Doe.
     *    Remark: BK-00042
     *    ..."
     *
     * We extract the remark and amount, then look for a matching pending
     * transaction and confirm it.
     *
     * @param  string $messageText  Raw text of the Telegram message
     * @return bool                 True if a matching transaction was confirmed
     */
    public function processIncomingMessage(string $messageText): bool
    {
        Log::info('AbaTelegramService: processing incoming Telegram message', [
            'message_preview' => mb_substr($messageText, 0, 200),
        ]);

        // -- Extract amount --------------------------------------------------
        // Matches "$25.00", "USD 25.00", "25.00 USD", etc.
        $amount = null;
        if (preg_match('/\$\s*(\d+(?:\.\d{1,2})?)/u', $messageText, $m)) {
            $amount = (float) $m[1];
        }

        // -- Extract booking reference from Remark ---------------------------
        // Expected format in the Remark field: "BK-00042" (see Booking::referenceNumber())
        if (! preg_match('/\bBK-(\d+)\b/i', $messageText, $rm)) {
            Log::warning('AbaTelegramService: no booking reference found in message');
            return false;
        }

        $bookingId = (int) $rm[1];

        // -- Find matching pending transaction -------------------------------
        $transaction = Transaction::where('booking_id', $bookingId)
            ->where('payment_method', Transaction::METHOD_TELEGRAM)
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();

        if (! $transaction) {
            Log::warning('AbaTelegramService: no matching pending transaction', [
                'booking_id' => $bookingId,
            ]);
            return false;
        }

        $booking = $transaction->booking;

        if (! $booking) {
            Log::error('AbaTelegramService: transaction has no booking', [
                'transaction_id' => $transaction->id,
            ]);
            return false;
        }

        // -- Confirm payment -------------------------------------------------
        $confirmedAmount = $amount ?? (float) $transaction->amount_paid;

        $newStatus = ($confirmedAmount + 0.01 >= (float) $booking->total_price)
            ? Transaction::STATUS_FULL
            : Transaction::STATUS_PARTIAL;

        $transaction->update([
            'amount_paid'    => $confirmedAmount,
            'payment_status' => $newStatus,
            'tracking_status' => 'TELEGRAM_CONFIRMED',
        ]);

        // Promote booking if still pending.
        if ($booking->booking_status === \App\Models\Booking::STATUS_PENDING) {
            $booking->update(['booking_status' => \App\Models\Booking::STATUS_BOOKED]);
        }

        Log::info('AbaTelegramService: payment confirmed via Telegram', [
            'booking_id'     => $bookingId,
            'transaction_id' => $transaction->id,
            'amount'         => $confirmedAmount,
            'new_status'     => $newStatus,
        ]);

        return true;
    }

    // -- Accessors ----------------------------------------------------------

    public function getAbaAccountNumber(): string
    {
        return $this->abaAccountNumber;
    }

    public function getGroupChatId(): string
    {
        return $this->groupChatId;
    }

    public function getBotToken(): string
    {
        return $this->botToken;
    }
}
