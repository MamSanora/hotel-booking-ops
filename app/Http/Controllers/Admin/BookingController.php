<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin BookingController
 *
 * Allows administrators to view all bookings and manage their lifecycle:
 *   - View full booking list with guest and room details
 *   - Approve a pending booking (pending → booked)
 *   - Cancel a booking (pending/booked → cancelled)
 *   - Delete a booking record entirely
 *
 * Route prefix: /admin/bookings
 */
class BookingController extends Controller
{
    /**
     * Display a paginated list of all bookings, newest first.
     */
    public function index(): View
    {
        $bookings = Booking::with(['guest', 'room', 'handledBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
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
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        if (! $booking->canCancel()) {
            return back()->with('error', 'Only pending or booked bookings can be cancelled.');
        }

        $isRefundable = $booking->isRefundable();
        $hasPaid = $booking->transactions()->where('payment_status', \App\Models\Transaction::STATUS_FULL)->exists();

        \Illuminate\Support\Facades\DB::transaction(function () use ($booking, $isRefundable, $hasPaid) {
            $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);

            if ($isRefundable && $hasPaid) {
                $booking->transactions()
                    ->where('payment_status', \App\Models\Transaction::STATUS_FULL)
                    ->update(['payment_status' => \App\Models\Transaction::STATUS_REFUNDED]);
            }
        });

        $message = "Booking {$booking->referenceNumber()} cancelled.";
        
        if ($hasPaid) {
            if ($isRefundable) {
                $message .= " Associated payments have been marked as refunded.";
            } else {
                $message .= " As this is within 24 hours of check-in, payments are non-refundable.";
            }
        }

        return back()->with('success', $message);
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
