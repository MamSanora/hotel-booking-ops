<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Transaction;
use App\Services\AbaPayWayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * PaymentController
 *
 * Manages the KHQR (ABA PayWay) checkout page for guest self-bookings.
 *
 * After a guest creates a booking (status=pending), they are redirected here.
 * This controller generates the ABA PayWay QR payload, stores it on the
 * pending Transaction record, and renders the QR code display page.
 *
 * Routes:
 *   GET  /payment/{booking}         → show()        — Display KHQR page
 *   POST /payment/{booking}/simulate → simulatePay() — Dev/demo helper
 */
class PaymentController extends Controller
{
    public function __construct(
        protected AbaPayWayService $payWayService
    ) {}

    /**
     * Show the ABA PayWay KHQR payment page for a booking.
     *
     * Generates (or regenerates) the QR payload and stores ABA transaction
     * details on the pending Transaction record for later callback matching.
     */
    public function show(Booking $booking): View
    {
        // Security: only the booking owner (or admin/staff) may view payment page.
        $guestId = Auth::guard('web')->check() ? Auth::guard('web')->user()->guest_id : null;

        if ($guestId && $booking->guest_id !== $guestId
            && ! Auth::guard('admin')->check()
            && ! Auth::guard('staff')->check()
        ) {
            abort(403, 'Unauthorized access to booking payment.');
        }

        $booking->load('room');

        // Generate ABA PayWay payload (tran_id, hash, QR URL, etc.)
        $payWayData = $this->payWayService->createPaymentData($booking);

        // Find the pending transaction for this booking (created during store()).
        // Store ABA-specific fields for callback matching and display.
        $transaction = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->latest()
            ->firstOrFail();

        $transaction->update([
            'transaction_id'     => $payWayData['transaction_id'],
            'merchant_reference' => $payWayData['merchant_reference'],
            'payment_link'       => $payWayData['payment_link'],
            'qr_code_url'        => $payWayData['qr_code_url'],
        ]);

        return view('payment.qr', compact('booking', 'transaction', 'payWayData'));
    }

    /**
     * Simulate a successful ABA PayWay callback for development / demo.
     *
     * Redirects to the callback URL with a 'success' status.
     * This must NEVER be reachable in production.
     */
    public function simulatePay(Request $request, Booking $booking): RedirectResponse
    {
        abort_if(app()->isProduction(), 403, 'Payment simulation is disabled in production.');

        $transaction = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();

        return redirect()->route('payment.callback', [
            'tran_id'    => $transaction?->transaction_id ?? ('DMH-TEST-' . time()),
            'status'     => 'success',
            'booking_id' => $booking->id,
        ]);
    }
}
