<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use App\Services\AbaPayWayService;
use App\Services\AbaTelegramService;
use App\Services\BakongApiService;
use App\Services\KhqrAbaStaticService;
use App\Services\KhqrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * PaymentController
 *
 * Acts as a unified payment router for guest self-bookings.
 * After a booking is created with a pending transaction, the guest is
 * redirected here. This controller inspects the transaction's payment_method
 * and routes to the appropriate payment flow:
 *
 *   'khqr'       → Generates a native KHQR string (Tag 29) and renders
 *                  the QR code display page with Bakong API polling.
 *   'aba_payway' → Calls AbaPayWayService to build a signed checkout payload
 *                  and renders an auto-submitting form to the ABA sandbox.
 *
 * Routes:
 *   GET  /payment/{booking}              → show()          — Route to correct payment UI
 *   GET  /payment/{booking}/check-status → checkStatus()   — AJAX polling (Bakong only)
 *   POST /payment/{booking}/simulate     → simulatePay()   — Dev/demo helper
 */
class PaymentController extends Controller
{
    public function __construct(
        protected KhqrService           $khqrService,
        protected BakongApiService      $bakongApiService,
        protected AbaPayWayService      $abaPayWayService,
        protected AbaTelegramService    $abaTelegramService,
        protected KhqrAbaStaticService  $khqrAbaStaticService,
    ) {}

    // ── Payment Gateway Router ─────────────────────────────────────────────

    /**
     * Inspect the pending transaction's payment_method and route to the
     * correct payment page.
     */
    public function show(Booking $booking): View|RedirectResponse|Response
    {
        $this->authorizeBookingAccess($booking);

        $booking->load('room');

        $transaction = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->latest()
            ->firstOrFail();

        // -- Global Payment Lock -----------------------------------------
        // Only one guest may be on the payment page at a time to prevent
        // the Telegram bot's amount fallback from crediting the wrong booking.
        $blocker = Transaction::getActiveLockFor($transaction->id);
        if ($blocker) {
            // Another guest currently holds the lock — show the busy screen.
            $expiresAt = $blocker->payment_lock_expires_at;
            return response()->make(
                view('payment.busy', compact('expiresAt'))
            );
        }

        // Acquire (or renew) the lock for this transaction.
        Transaction::acquireLock($transaction->id);
        // ----------------------------------------------------------------

        if ($booking->room->roomType->use_mam_sanora_qr) {
            return $this->showMamSanoraStatic($booking, $transaction);
        }

        return match ($transaction->payment_method) {
            Transaction::METHOD_ABA      => $this->showPayWay($booking, $transaction),
            Transaction::METHOD_TELEGRAM => $this->showAbaTelegram($booking, $transaction),
            Transaction::METHOD_KHQR_ABA => $this->showKhqrAbaStatic($booking, $transaction),
            default                      => $this->showKhqr($booking, $transaction),
        };
    }

    // ── Bakong KHQR Flow ───────────────────────────────────────────────────

    /**
     * Generate a KHQR string and render the QR code payment page.
     */
    protected function showKhqr(Booking $booking, Transaction $transaction): View
    {
        $khqrData = $this->khqrService->generate($booking, $transaction->amount_paid);

        $transaction->update([
            'khqr_string' => $khqrData['khqr_string'],
            'md5_hash'    => $khqrData['md5_hash'],
        ]);

        return view('payment.qr', compact('booking', 'transaction', 'khqrData'));
    }

    // ── ABA PayWay Flow ────────────────────────────────────────────────────

    /**
     * Call ABA PayWay API to register the transaction and retrieve the QR code,
     * then render the ABA-branded payment page.
     *
     * ABA PayWay is a server-side API (not hosted checkout):
     *   1. Our server POSTs to ABA → ABA returns a QR code image + deeplink.
     *   2. The transaction is immediately registered in ABA's sandbox dashboard.
     *   3. We display the QR so the guest can scan with ABA Mobile.
     *   4. On payment, ABA POSTs to /payment/callback and redirects the browser.
     *
     * For sandbox testing: after loading this page, go to the ABA PayWay
     * sandbox dashboard and click "Simulate Payment" next to the pending transaction.
     */
    protected function showPayWay(Booking $booking, Transaction $transaction): View|RedirectResponse
    {
        $paymentData = $this->abaPayWayService->createPaymentData($booking, $transaction->amount_paid ?: null);

        if (! $paymentData['api_success']) {
            return redirect()->route('payment.show', $booking->id)
                ->withErrors(['payment' => 'ABA PayWay error: ' . $paymentData['api_error']]);
        }

        // Persist the ABA transaction ID so the callback can match it.
        $transaction->update([
            'transaction_id' => $paymentData['transaction_id'],
        ]);

        return view('payment.payway-qr', compact('booking', 'transaction', 'paymentData'));
    }

    // ── ABA Telegram Transfer Flow ─────────────────────────────────────────

    /**
     * Display the ABA Telegram payment instruction page.
     *
     * No API call needed — we just show the hotel's ABA account number and
     * instruct the guest to include their booking reference in the remark.
     * Payment confirmation happens asynchronously via the Telegram webhook.
     */
    protected function showAbaTelegram(Booking $booking, Transaction $transaction): View
    {
        $abaAccountNumber = $this->abaTelegramService->getAbaAccountNumber();
        $reference        = $booking->referenceNumber();
        $khqrString       = null;

        if ($booking->room->roomType->use_mam_sanora_qr) {
            $amount = $transaction->amount_paid > 0
                ? $transaction->amount_paid
                : $booking->depositAmount();
            
            $merchantName = config('telegram.aba_merchant_name_2', 'MAM SANORA');
            $accountNumber = config('telegram.aba_account_number_2', '126072315150668');
            
            $abaAccountNumber = $accountNumber; // Override the text display
            
            // Mam Sanora credentials for Test Room
            $khqrString = \App\Services\KhqrGenerator::generate($merchantName, $accountNumber, $amount, '840', $reference);
        }

        return view('payment.telegram-transfer', compact(
            'booking',
            'transaction',
            'abaAccountNumber',
            'reference',
            'khqrString'
        ));
    }

    // ── KHQR & ABA Pay Dynamic Flow ──────────────────────────────────────────

    /**
     * Display the dynamic KHQR payment page.
     *
     * The QR code is generated on-the-fly using the ABA merchant account
     * credentials stored in config/telegram.php (TELEGRAM_ABA_ACCOUNT_NUMBER
     * and TELEGRAM_ABA_MERCHANT_NAME). No pre-uploaded images are required.
     *
     * Payment confirmation is manual: the receptionist verifies the transfer
     * notification in the hotel owner's ABA app, then marks the booking as
     * paid via the Reception dashboard.
     */
    protected function showKhqrAbaStatic(Booking $booking, Transaction $transaction): View
    {
        $amount = $transaction->amount_paid > 0
            ? $transaction->amount_paid
            : $booking->depositAmount();

        // Inject the booking reference into the KHQR so ABA sets it as the transfer remark!
        $khqrString = \App\Services\KhqrGenerator::forAmount($amount, $booking->referenceNumber());

        return view('payment.payway-static-qr', compact(
            'booking',
            'transaction',
            'khqrString',
        ));
    }
    
    protected function showMamSanoraStatic(Booking $booking, Transaction $transaction): View
    {
        $amount = $transaction->amount_paid > 0
            ? $transaction->amount_paid
            : $booking->depositAmount();

        $khqrString = \App\Services\KhqrGenerator::forMamSanora($amount, $booking->referenceNumber());

        return view('payment.payway-static-qr', compact(
            'booking',
            'transaction',
            'khqrString',
        ));
    }


    // ── Status Polling Endpoint (Local Database verification via Telegram) ──

    /**
     * AJAX polling endpoint — check if a Telegram payment has been received.
     *
     * The frontend calls this every few seconds. It checks if the
     * TelegramWebhookController has marked the transaction as paid.
     *
     * All complex booking promotion logic (room reassignment, overpayment
     * detection) is handled server-side inside AbaTelegramService when the
     * webhook fires — this endpoint only needs to report the final result.
     *
     * Returns JSON:
     *   { "paid": false }
     *   { "paid": true, "redirect": "/payment/success/123" }
     *   { "paid": true, "redirect": "/payment/failed" }  ← fully sold out
     */
    public function checkStatus(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeBookingAccess($booking);

        // Refresh the booking from DB so we get the latest status set by the webhook.
        $booking->refresh();

        // If there is still a pending transaction, payment has not been received yet.
        // This covers both regular bookings AND stay extensions (which don't change
        // booking_status — the booking stays as 'checked-in' while payment is pending).
        $pendingTransaction = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();

        if ($pendingTransaction) {
            return response()->json(['paid' => false]);
        }

        // Payment was received but the room type was fully sold out — refund required.
        if ($booking->booking_status === Booking::STATUS_SNATCHED) {
            return response()->json([
                'paid'     => true,
                'redirect' => route('payment.failed'),
            ]);
        }

        // No pending transaction and not snatched — payment was confirmed.
        return response()->json([
            'paid'     => true,
            'redirect' => route('payment.success', $booking->id),
        ]);
    }

    /**
     * Unlock the payment transaction when the guest explicitly exits the page.
     * This receives a beacon request from the frontend when the tab is closed
     * or the user navigates away.
     */
    public function unlock(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeBookingAccess($booking);

        $pendingTransaction = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();

        if ($pendingTransaction) {
            Transaction::releaseLock($pendingTransaction->id);
            return response()->json(['unlocked' => true]);
        }

        return response()->json(['unlocked' => false]);
    }

    // ── Dev / Demo Helper ──────────────────────────────────────────────────

    /**
     * Simulate a successful payment for development / demo.
     *
     * Mirrors the exact same code path as the real Telegram webhook so that
     * dev testing (race conditions, auto-reassign, overpayments) is accurate.
     * Must NEVER be reachable in production.
     */
    public function simulatePay(Request $request, Booking $booking): RedirectResponse
    {
        abort_if(app()->isProduction(), 403, 'Payment simulation is disabled in production.');

        $transaction = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();

        if ($transaction) {
            $amount = $transaction->amount_paid > 0
                ? $transaction->amount_paid
                : $booking->depositAmount();

            $transaction->update([
                'amount_paid'     => $amount,
                'payment_status'  => ($amount + 0.01 >= (float) $booking->total_price)
                    ? Transaction::STATUS_FULL
                    : Transaction::STATUS_PARTIAL,
                'tracking_status' => 'SIMULATED',
            ]);
        }

        // Use the same promotion logic as the real webhook for dev parity.
        if ($transaction && $transaction->payment_for === Transaction::FOR_STAY_EXTENSION) {
            $this->abaTelegramService->applyStayExtension($booking, $transaction);
        } elseif ($booking->booking_status === Booking::STATUS_PENDING) {
            $this->abaTelegramService->promoteBookingAfterPayment($booking);
        }

        $booking->refresh();

        if ($booking->booking_status === Booking::STATUS_SNATCHED) {
            return redirect()->route('payment.failed')
                ->with('error', 'Simulated: payment received but room type was fully sold out. Refund required.');
        }

        return redirect()->route('payment.success', $booking->id)
            ->with('info', 'Payment simulated (demo mode).');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Abort with 403 if the authenticated guest doesn't own this booking.
     * Admin and staff guards are allowed through.
     */
    protected function authorizeBookingAccess(Booking $booking): void
    {
        $guestId = Auth::guard('web')->check() ? Auth::guard('web')->user()->guest_id : null;

        if ($guestId
            && $booking->guest_id !== $guestId
            && ! Auth::guard('admin')->check()
            && ! Auth::guard('staff')->check()
        ) {
            abort(403, 'Unauthorized access to booking payment.');
        }
    }
}
