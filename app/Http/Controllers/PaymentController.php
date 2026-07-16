<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use App\Services\AbaPayWayService;
use App\Services\BakongApiService;
use App\Services\KhqrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        protected KhqrService      $khqrService,
        protected BakongApiService $bakongApiService,
        protected AbaPayWayService $abaPayWayService,
    ) {}

    // ── Payment Gateway Router ─────────────────────────────────────────────

    /**
     * Inspect the pending transaction's payment_method and route to the
     * correct payment page.
     */
    public function show(Booking $booking): View
    {
        $this->authorizeBookingAccess($booking);

        $booking->load('room');

        $transaction = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->latest()
            ->firstOrFail();

        return match ($transaction->payment_method) {
            Transaction::METHOD_ABA  => $this->showPayWay($booking, $transaction),
            default                  => $this->showKhqr($booking, $transaction),
        };
    }

    // ── Bakong KHQR Flow ───────────────────────────────────────────────────

    /**
     * Generate a KHQR string and render the QR code payment page.
     */
    protected function showKhqr(Booking $booking, Transaction $transaction): View
    {
        $khqrData = $this->khqrService->generate($booking);

        $transaction->update([
            'khqr_string' => $khqrData['khqr_string'],
            'md5_hash'    => $khqrData['md5_hash'],
        ]);

        return view('payment.qr', compact('booking', 'transaction', 'khqrData'));
    }

    // ── ABA PayWay Flow ────────────────────────────────────────────────────

    protected function showPayWay(Booking $booking, Transaction $transaction): View|RedirectResponse
    {
        $paymentData = $this->abaPayWayService->createPaymentData($booking);

        if (! $paymentData['api_success']) {
            return redirect()->route('payment.show', $booking->id)
                ->withErrors(['payment' => 'ABA PayWay error: ' . $paymentData['api_error']]);
        }

        // Persist the ABA transaction ID so we can match it on callback.
        $transaction->update([
            'transaction_id' => $paymentData['transaction_id'],
        ]);

        return view('payment.payway-qr', compact('booking', 'transaction', 'paymentData'));
    }

    // ── Status Polling Endpoint (Bakong only) ──────────────────────────────

    /**
     * AJAX polling endpoint — check if a KHQR payment has been received.
     *
     * The frontend calls this every few seconds. We query the Bakong Open
     * API using the md5_hash. If paid, we mark the transaction as full and
     * the booking as booked, then return a redirect URL for the frontend.
     *
     * Returns JSON:
     *   { "paid": false }
     *   { "paid": true, "redirect": "/payment/success/123" }
     */
    public function checkStatus(Request $request, Booking $booking): JsonResponse
    {
        $this->authorizeBookingAccess($booking);

        // If already confirmed (booked or still checked-in after an extension), return success.
        if (in_array($booking->booking_status, [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])) {
            // Only redirect to success immediately if there are no more pending transactions.
            $hasPending = $booking->transactions()
                ->where('payment_status', Transaction::STATUS_PENDING)
                ->exists();

            if (! $hasPending) {
                return response()->json([
                    'paid'     => true,
                    'redirect' => route('payment.success', $booking->id),
                ]);
            }
        }

        $transaction = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->where('payment_method', Transaction::METHOD_KHQR)
            ->latest()
            ->first();

        if (! $transaction || ! $transaction->md5_hash) {
            return response()->json(['paid' => false]);
        }

        $isPaid = $this->bakongApiService->checkPayment($transaction);

        if ($isPaid) {
            // Use the transaction's own amount_paid which already holds the correct
            // deposit for the tier (set during booking creation in RoomController).
            $transaction->update([
                'amount_paid'    => $transaction->amount_paid > 0
                    ? $transaction->amount_paid  // already set correctly
                    : $booking->depositAmount(), // fallback safety
                'payment_status' => Transaction::STATUS_FULL,
            ]);

            // Only promote to 'booked' if the booking was still pending.
            // A checked-in guest paying for an extension stays checked-in.
            if ($booking->booking_status === Booking::STATUS_PENDING) {
                $room = \App\Models\Room::find($booking->room_id);
                // Race condition check: is there now a SAME-tier (or higher) booking
                // that completed payment before this one? Pass the booking's tier so
                // we don't cancel just because a lower-tier double-booking exists.
                if (!$room || !$room->isAvailableForDates(
                    $booking->check_in_date,
                    $booking->check_out_date,
                    $booking->id,
                    $booking->payment_tier
                )) {
                    $booking->update([
                        'booking_status'   => Booking::STATUS_SNATCHED,
                        'special_requests' => '[RACE CONDITION: SAME-TIER BOOKING SNATCHED. REFUND REQUIRED] ' . $booking->special_requests,
                    ]);

                    return response()->json([
                        'paid'     => true,
                        'redirect' => route('payment.failed'),
                    ]);
                }

                $booking->update(['booking_status' => Booking::STATUS_BOOKED]);
            }

            return response()->json([
                'paid'     => true,
                'redirect' => route('payment.success', $booking->id),
            ]);
        }

        return response()->json(['paid' => false]);
    }

    // ── Dev / Demo Helper ──────────────────────────────────────────────────

    /**
     * Simulate a successful payment for development / demo.
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
            $transaction->update([
                'amount_paid'     => $transaction->amount_paid > 0
                    ? $transaction->amount_paid
                    : $booking->depositAmount(),
                'payment_status'  => Transaction::STATUS_FULL,
                'tracking_status' => 'SIMULATED',
            ]);
        }

        // Only promote to 'booked' if the booking hasn't advanced further.
        // A checked-in guest paying for an extension stays checked-in.
        if ($booking->booking_status === Booking::STATUS_PENDING) {
            $room = \App\Models\Room::find($booking->room_id);
            if (!$room || !$room->isAvailableForDates(
                $booking->check_in_date,
                $booking->check_out_date,
                $booking->id,
                $booking->payment_tier
            )) {
                $booking->update([
                    'booking_status'   => Booking::STATUS_SNATCHED,
                    'special_requests' => '[RACE CONDITION: SAME-TIER BOOKING SNATCHED. REFUND REQUIRED] ' . $booking->special_requests,
                ]);

                return redirect()->route('payment.failed')
                    ->with('error', 'Payment simulated, but the room was booked by someone else at the same tier moments ago! Refund required.');
            }

            $booking->update(['booking_status' => Booking::STATUS_BOOKED]);
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
