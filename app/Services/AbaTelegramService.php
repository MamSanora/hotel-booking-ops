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
 *   isConfigured() → TELEGRAM_BOT_TOKEN is set in .env
 *   isReachable()  → Telegram Bot API /getMe responds with ok:true
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
            $response = Http::connectTimeout(2)->timeout(3)
                ->get(self::TELEGRAM_API . "/bot{$this->botToken}/getMe");

            return $response->successful() && ($response->json('ok') === true);
        } catch (\Throwable) {
            return false;
        }
    }

    // -- Payment Verification -----------------------------------------------

    /**
     * Parse a Telegram message text (forwarded from ABA bot) and attempt to
     * match it against a transaction by the booking reference embedded in the
     * "Remark" field.
     *
     * ABA notification messages generally look like:
     *   "You received $25.00 from John Doe.
     *    Remark: BK-00042
     *    ..."
     *
     * Handles two scenarios:
     *   1. Normal first payment   — pending transaction exists → confirm it,
     *                               then promote the booking and assign a room.
     *   2. Duplicate / overpayment — no pending transaction found → record a
     *                               new overpayment transaction and alert staff
     *                               via special_requests so the money can be
     *                               refunded on arrival.
     *
     * @param  string $messageText  Raw text of the Telegram message
     * @return bool                 True if a matching transaction was handled
     */
    public function processIncomingMessage(string $messageText): bool
    {
        Log::info('AbaTelegramService: processing incoming Telegram message', [
            'message_preview' => mb_substr($messageText, 0, 200),
        ]);

        // -- Extract amount --------------------------------------------------
        // Matches "$0.02", "$30.00", etc. from ABA PayWay message format.
        $amount = null;
        if (preg_match('/\$\s*(\d+(?:\.\d{1,2})?)/u', $messageText, $m)) {
            $amount = (float) $m[1];
        }

        // -- Extract APV code ------------------------------------------------
        // ABA PayWay always includes "APV: 537035" (6 digits) in its message.
        $apvCode = null;
        if (preg_match('/APV:\s*(\d+)/u', $messageText, $apvMatch)) {
            $apvCode = $apvMatch[1];
        }

        // -- Extract Transaction Number --------------------------------------
        // ABA PayWay includes "លេខប្រតិបត្តិការ: 178516144530426" (15 digits).
        // This is globally unique and acts as an irrefutable payment reference.
        $transactionNumber = null;
        if (preg_match('/(\d{10,20})/', $messageText, $txnMatch)) {
            $transactionNumber = $txnMatch[1];
        }

        Log::info('AbaTelegramService: extracted payment identifiers', [
            'amount'             => $amount,
            'apv_code'           => $apvCode,
            'transaction_number' => $transactionNumber,
        ]);

        // -- Duplicate payment guard ----------------------------------------
        // If we already processed this exact ABA transaction number, ignore it.
        if ($transactionNumber) {
            $alreadyProcessed = Transaction::where('tracking_status', 'LIKE', '%' . $transactionNumber . '%')
                ->exists();
            if ($alreadyProcessed) {
                Log::info('AbaTelegramService: duplicate transaction number detected, ignoring.', [
                    'transaction_number' => $transactionNumber,
                ]);
                return false;
            }
        }

        // -- Extract booking reference from Remark ---------------------------
        // Expected format in the Remark field: "BK-00042" (see Booking::referenceNumber())
        $transaction = null;
        $bookingId = null;

        if (preg_match('/\bBK-(\d+)\b/i', $messageText, $rm)) {
            $bookingId = (int) $rm[1];
            
            // -- Find matching pending transaction -------------------------------
            $transaction = Transaction::where('booking_id', $bookingId)
                ->whereIn('payment_method', [Transaction::METHOD_TELEGRAM, Transaction::METHOD_KHQR, Transaction::METHOD_KHQR_ABA])
                ->where('payment_status', Transaction::STATUS_PENDING)
                ->latest()
                ->first();
        } else {
            // -- Fallback: Match by Exact Amount --------------------------------
            // ABA PayWay Merchant bots strip the BK- reference from Telegram receipts.
            // In this case, we find the pending transaction matching the exact amount.
            if ($amount) {
                Log::info('AbaTelegramService: No BK- reference found. Attempting fallback match by exact amount: $' . $amount);

                // FIFO: Credit the person who has been waiting the longest first.
                // Restrict to active bookings only (pending/booked/checked-in) so
                // stale pending txns from abandoned/cancelled bookings don't match.
                $transaction = Transaction::where('payment_status', Transaction::STATUS_PENDING)
                    ->whereIn('payment_method', [Transaction::METHOD_TELEGRAM, Transaction::METHOD_KHQR, Transaction::METHOD_KHQR_ABA])
                    ->where('amount_paid', $amount)
                    ->whereHas('booking', fn ($q) => $q->whereIn('booking_status', [
                        \App\Models\Booking::STATUS_PENDING,
                        \App\Models\Booking::STATUS_BOOKED,
                        \App\Models\Booking::STATUS_CHECKED_IN,
                    ]))
                    ->oldest()
                    ->first();

                if ($transaction) {
                    $bookingId = $transaction->booking_id;
                    Log::info('AbaTelegramService: Fallback match successful for Booking ID: ' . $bookingId);
                }
            }
        }

        // -- Overpayment / duplicate payment detection -----------------------
        // If there is no pending transaction, the booking was already paid (or not found).
        if (! $transaction) {
            if ($bookingId) {
                return $this->handleOverpayment($bookingId, $amount);
            }
            Log::warning('AbaTelegramService: No pending transaction found for this payment message.');
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

        // Build tracking status with ABA's unique identifiers for full audit trail.
        $trackingParts = ['TELEGRAM_CONFIRMED'];
        if ($apvCode)           { $trackingParts[] = 'APV:' . $apvCode; }
        if ($transactionNumber) { $trackingParts[] = 'TXN:' . $transactionNumber; }

        $transaction->update([
            'amount_paid'     => $confirmedAmount,
            'payment_status'  => $newStatus,
            'tracking_status' => implode('|', $trackingParts),
        ]);

        Log::info('AbaTelegramService: payment confirmed via Telegram', [
            'booking_id'     => $bookingId,
            'transaction_id' => $transaction->id,
            'amount'         => $confirmedAmount,
            'new_status'     => $newStatus,
            'payment_for'    => $transaction->payment_for,
        ]);

        // -- Release global payment lock ------------------------------------
        // Frees the queue for the next guest immediately rather than waiting
        // for the 1-minute heartbeat expiry.
        \App\Models\Transaction::releaseLock($transaction->id);

        // -- Post-payment actions based on what was paid for ---------------
        if ($transaction->payment_for === Transaction::FOR_STAY_EXTENSION) {
            // Apply the extension to the booking now that payment is confirmed.
            $this->applyStayExtension($booking, $transaction);
        } elseif ($booking->booking_status === \App\Models\Booking::STATUS_PENDING) {
            // Promote a new booking from pending → booked (or snatched).
            $this->promoteBookingAfterPayment($booking);
        }

        return true;
    }

    // -- Booking Promotion Logic --------------------------------------------

    /**
     * Promote a booking from 'pending' to 'booked' after a confirmed payment.
     *
     * Race Condition Handling:
     *   Checks if the currently assigned room is still available. If it has
     *   been snatched (another guest at the same tier paid first), the system
     *   automatically searches for any other available room of the SAME type.
     *
     *   - If an alternate room is found  → silently reassign and mark 'booked'.
     *     The guest never experiences a failure; the receptionist sees the new
     *     room number in the dashboard.
     *   - If no alternate room exists    → mark as 'snatched' and flag for a
     *     refund. Only happens in a fully-sold-out scenario for that room type.
     *
     * @param  \App\Models\Booking $booking
     */
    public function promoteBookingAfterPayment(\App\Models\Booking $booking): void
    {
        $room = \App\Models\Room::find($booking->room_id);

        $roomIsAvailable = $room && $room->isAvailableForDates(
            $booking->check_in_date,
            $booking->check_out_date,
            $booking->id,
            $booking->payment_tier
        );

        if ($roomIsAvailable) {
            // Happy path: the room is still free.
            $booking->update(['booking_status' => \App\Models\Booking::STATUS_BOOKED]);

            Log::info('AbaTelegramService: booking promoted to booked', [
                'booking_id' => $booking->id,
                'room_id'    => $booking->room_id,
            ]);
            return;
        }

        // The assigned room was snatched. Try to find another of the same type.
        $roomTypeId    = $room?->room_type_id ?? null;
        $alternateRoom = null;

        if ($roomTypeId) {
            $alternateRoom = \App\Models\Room::where('room_type_id', $roomTypeId)
                ->where('id', '!=', $booking->room_id)
                ->availableForDates(
                    $booking->check_in_date,
                    $booking->check_out_date,
                    null,
                    $booking->payment_tier
                )
                ->first();
        }

        if ($alternateRoom) {
            // Auto-reassign to the alternate room. Guest is unaffected.
            $booking->update([
                'room_id'        => $alternateRoom->id,
                'booking_status' => \App\Models\Booking::STATUS_BOOKED,
            ]);

            Log::info('AbaTelegramService: room snatched, auto-reassigned to alternate', [
                'booking_id'       => $booking->id,
                'original_room_id' => $room?->id,
                'new_room_id'      => $alternateRoom->id,
            ]);
        } else {
            // Fully sold out for this room type. Staff must manually refund.
            $booking->update([
                'booking_status'   => \App\Models\Booking::STATUS_SNATCHED,
                'special_requests' => '[REFUND REQUIRED: Room type fully booked. No alternate available.] '
                    . $booking->special_requests,
            ]);

            Log::warning('AbaTelegramService: booking snatched, no alternate room found', [
                'booking_id'   => $booking->id,
                'room_type_id' => $roomTypeId,
            ]);
        }
    }

    // -- Stay Extension Application -----------------------------------------

    /**
     * Apply a confirmed stay extension to the booking.
     *
     * Called after the webhook confirms a stay_extension transaction.
     * The extension_nights and extension_new_checkout were stored on the
     * transaction by extendStay() so we don't have to recalculate here.
     *
     * @param  \App\Models\Booking     $booking
     * @param  \App\Models\Transaction $transaction
     */
    public function applyStayExtension(\App\Models\Booking $booking, Transaction $transaction): void
    {
        if (! $transaction->extension_new_checkout || ! $transaction->extension_nights) {
            Log::error('AbaTelegramService: stay_extension transaction missing metadata', [
                'transaction_id' => $transaction->id,
            ]);
            return;
        }

        $extraCost = (float) $transaction->amount_paid;

        $booking->update([
            'check_out_date'           => $transaction->extension_new_checkout,
            'total_price'              => (float) $booking->total_price + $extraCost,
            'number_of_stay_extension' => $booking->number_of_stay_extension + 1,
        ]);

        Log::info('AbaTelegramService: stay extension applied to booking', [
            'booking_id'      => $booking->id,
            'extra_nights'    => $transaction->extension_nights,
            'new_checkout'    => $transaction->extension_new_checkout,
            'extra_cost'      => $extraCost,
        ]);
    }

    // -- Overpayment Handling -----------------------------------------------


    /**
     * Handle a duplicate / second payment for an already-confirmed booking.
     *
     * Creates a new transaction record with tracking_status = 'OVERPAYMENT'
     * and appends an alert to special_requests so the receptionist sees it
     * immediately when the guest checks in.
     *
     * @param  int        $bookingId
     * @param  float|null $amount     Amount parsed from the Telegram message
     * @return bool
     */
    protected function handleOverpayment(int $bookingId, ?float $amount): bool
    {
        $booking = \App\Models\Booking::find($bookingId);

        if (! $booking) {
            Log::warning('AbaTelegramService: overpayment received for unknown booking', [
                'booking_id' => $bookingId,
            ]);
            return false;
        }

        $overpaidAmount = $amount ?? 0.00;

        // Record the duplicate payment so accounting has a clear trail.
        Transaction::create([
            'booking_id'      => $bookingId,
            'amount_paid'     => $overpaidAmount,
            'payment_for'     => Transaction::FOR_BOOKING,
            'payment_method'  => Transaction::METHOD_TELEGRAM,
            'payment_status'  => Transaction::STATUS_FULL,
            'tracking_status' => 'OVERPAYMENT',
        ]);

        // Append an alert to special_requests so it's visible on the reception dashboard.
        $alert = '[OVERPAYMENT ALERT: $' . number_format($overpaidAmount, 2) . ' received twice. Refund on arrival.]';
        $booking->update([
            'special_requests' => trim($alert . ' ' . $booking->special_requests),
        ]);

        Log::warning('AbaTelegramService: overpayment detected and recorded', [
            'booking_id'      => $bookingId,
            'overpaid_amount' => $overpaidAmount,
        ]);

        return true;
    }

    // -- Messaging ----------------------------------------------------------

    /**
     * Send a text message back to a specific Telegram chat.
     */
    public function sendMessage(string $text, string $chatId = null): void
    {
        if (! $this->isConfigured()) return;
        
        $chatId = $chatId ?? $this->groupChatId;
        
        try {
            Http::timeout(5)->post(self::TELEGRAM_API . "/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text'    => $text,
            ]);
        } catch (\Throwable $e) {
            Log::error('AbaTelegramService: failed to send message', [
                'error' => $e->getMessage(),
            ]);
        }
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
