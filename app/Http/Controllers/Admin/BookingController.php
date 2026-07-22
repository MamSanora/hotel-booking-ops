<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin BookingController
 *
 * Allows administrators to view all bookings and manage their lifecycle:
 *   - View full booking list with guest and room details
 *   - Approve a pending booking (pending → booked)
 *   - Cancel a booking (pending/booked → cancelled)
 *   - Mark a cancelled booking's payment as refunded (bookkeeping only)
 *   - Delete a booking record entirely
 *
 * Route prefix: /admin/bookings
 */
class BookingController extends Controller
{
    /**
     * Display a paginated list of all bookings, newest first.
     * Also passes a count of bookings that need a refund for the alert banner.
     */
    public function index(): View
    {
        $bookings = Booking::with(['guest', 'room', 'handledBy', 'transactions'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // Count cancelled bookings that have a full (paid) transaction but no refunded one yet.
        // These represent real money the hotel owes back to a guest.
        $pendingRefundCount = Booking::where('booking_status', Booking::STATUS_CANCELLED)
            ->whereHas('transactions', fn ($q) => $q->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL]))
            ->whereDoesntHave('transactions', fn ($q) => $q->where('payment_status', Transaction::STATUS_REFUNDED))
            ->count();

        return view('admin.bookings.index', compact('bookings', 'pendingRefundCount'));
    }

    /**
     * Approve a pending booking — marks it as 'booked' (payment confirmed).
     * In normal flow, booking moves to 'booked' after KHQR payment is verified.
     * Admin approval is for cases requiring manual confirmation.
     */
    public function approve(Booking $booking): RedirectResponse
    {
        if (! $booking->isPending()) {
            return back()->with('error', 'Only pending bookings can be approved.');
        }

        $booking->update(['booking_status' => Booking::STATUS_BOOKED]);

        return back()->with('success', "Booking {$booking->referenceNumber()} approved.");
    }

    /**
     * Cancel a booking (admin-initiated).
     * Allowed for pending and booked bookings only.
     *
     * NOTE: This does NOT automatically mark transactions as refunded.
     * If the guest paid digitally, the owner must physically send money back
     * through ABA/Bakong, then use the "Mark as Refunded" action below.
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        if (! $booking->canCancel()) {
            return back()->with('error', 'Only pending or booked bookings can be cancelled.');
        }

        $hasPaid = $booking->transactions()
            ->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL])
            ->exists();

        $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);

        $message = "Booking {$booking->referenceNumber()} cancelled.";

        if ($hasPaid) {
            $message .= ' This guest had a completed payment — please issue a manual refund via ABA/Bakong, then mark it as refunded here.';
        }

        return back()->with('success', $message);
    }

    /**
     * Mark a cancelled booking's payment as refunded (bookkeeping only).
     *
     * This does NOT move any money. The owner must have already manually
     * transferred the refund via ABA/Bakong before clicking this button.
     * This action simply records that the refund has been issued, so the
     * hotel's records match the actual bank statement.
     */
    public function markAsRefunded(Booking $booking): RedirectResponse
    {
        if (! $booking->isCancelled()) {
            return back()->with('error', 'Only cancelled bookings can be marked as refunded.');
        }

        $fullTransactions = $booking->transactions()
            ->whereIn('payment_status', [Transaction::STATUS_FULL, Transaction::STATUS_PARTIAL])
            ->get();

        if ($fullTransactions->isEmpty()) {
            return back()->with('error', 'No completed payment found on this booking — nothing to refund.');
        }

        $alreadyRefunded = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_REFUNDED)
            ->exists();

        if ($alreadyRefunded) {
            return back()->with('error', "Booking {$booking->referenceNumber()} has already been marked as refunded.");
        }

        DB::transaction(function () use ($fullTransactions) {
            foreach ($fullTransactions as $transaction) {
                $transaction->update(['payment_status' => Transaction::STATUS_REFUNDED]);
            }
        });

        return back()->with('success', "Booking {$booking->referenceNumber()} marked as refunded. Bookkeeping updated.");
    }

    /**
     * Permanently delete a booking record.
     * Should only be used for erroneous or test records.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $ref = $booking->referenceNumber();
        $booking->delete();

        return back()->with('success', "Booking {$ref} deleted.");
    }
}
