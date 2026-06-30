<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use App\Services\AbaPayWayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PaymentCallbackController
 *
 * Receives ABA PayWay's response after a guest completes (or abandons) payment.
 *
 * Two entry points:
 *   GET  /payment/callback — ABA redirects the guest's browser here after payment
 *   POST /payment/callback — ABA server-to-server webhook (optional IPN)
 *
 * Security rule:
 *   Booking status NEVER advances based on the URL parameters alone.
 *   We ALWAYS call verifyTransaction() to validate ABA's cryptographic response
 *   before marking a booking as paid.
 */
class PaymentCallbackController extends Controller
{
    public function __construct(
        protected AbaPayWayService $payWayService
    ) {}

    /**
     * Handle the ABA PayWay callback.
     *
     * ABA sends these parameters:
     *   tran_id    — Our merchant transaction reference (stored on Transaction)
     *   status     — '0' / '00' / 0 = success; anything else = failure
     *   apv        — Bank approval code
     *   booking_id — Passed via query string (our own convention for easy lookup)
     */
    public function handle(Request $request): RedirectResponse
    {
        Log::info('ABA PayWay Callback Received', $request->all());

        $tranId    = $request->input('tran_id', '');
        $bookingId = $request->input('booking_id');

        // Find the transaction by ABA's tran_id, fallback to booking_id.
        $transaction = null;

        if ($tranId) {
            $transaction = Transaction::where('transaction_id', $tranId)->first();
        }

        if (! $transaction && $bookingId) {
            $transaction = Transaction::where('booking_id', $bookingId)->latest()->first();
        }

        if (! $transaction) {
            Log::error('ABA Callback: No transaction record found', [
                'tran_id'    => $tranId,
                'booking_id' => $bookingId,
            ]);

            return redirect()->route('payment.failed')
                ->with('error', 'Payment record not found. Please contact hotel staff.');
        }

        $booking = Booking::findOrFail($transaction->booking_id);

        // Verify the transaction cryptographically via the service class.
        $isVerified = $this->payWayService->verifyTransaction($tranId, $request->all());

        if ($isVerified) {
            // ── Payment Verified ────────────────────────────────────────────
            $transaction->update([
                'amount_paid'    => $booking->total_price,
                'payment_status' => Transaction::STATUS_FULL,
            ]);

            // Booking transitions from 'pending' → 'booked' (confirmed + paid).
            $booking->update(['booking_status' => Booking::STATUS_BOOKED]);

            Log::info('ABA Callback: Payment verified — booking confirmed', [
                'booking_id'     => $booking->id,
                'transaction_id' => $tranId,
            ]);

            return redirect()->route('payment.success', $booking->id);

        } else {
            // ── Payment Failed ──────────────────────────────────────────────
            $transaction->update(['payment_status' => Transaction::STATUS_PENDING]);

            // Keep booking as 'pending' — guest can retry payment.
            Log::warning('ABA Callback: Payment verification failed', [
                'booking_id'     => $booking->id,
                'transaction_id' => $tranId,
                'raw_status'     => $request->input('status', ''),
            ]);

            return redirect()
                ->route('payment.failed', ['booking_id' => $booking->id])
                ->with('error', 'Payment was not successful. Please try again or contact hotel staff.');
        }
    }
}
