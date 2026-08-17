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
    public function index(\Illuminate\Http\Request $request): View
    {
        // Count cancelled bookings whose transactions were explicitly flagged
        // as refund_pending by the cancel() controller
        $pendingRefundCount = Booking::where('booking_status', Booking::STATUS_CANCELLED)
            ->whereHas('transactions', fn ($q) => $q->where('payment_status', Transaction::STATUS_REFUND_PENDING))
            ->count();

        return view('admin.bookings.index', compact('pendingRefundCount'));
    }

    /**
     * View POS-style receipt for a booking.
     */
    public function receipt(Booking $booking): View
    {
        $booking->load([
            'guest',
            'bookingRooms.roomType',
            'transactions',
            'bookingRooms.roomType',
            'incidentalCharges',
        ]);

        $latestTxn    = $booking->transactions->sortByDesc('created_at')->first();
        $exchangeRate = \App\Models\ExchangeRate::usdToKhr()->value('rate') ?? 4100;

        return view('admin.bookings.receipt', compact('booking', 'latestTxn', 'exchangeRate'));
    }

    /**
     * Export bookings to Excel.
     */
    public function export(\Illuminate\Http\Request $request)
    {
        $query = Booking::query();

        // 1. Search by Booking Reference or Guest Name/Email/Phone
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $numericSearch = preg_replace('/[^0-9]/', '', $search);
                if (!empty($numericSearch)) {
                    $q->where('id', 'like', "%{$numericSearch}%");
                }
                
                $q->orWhereHas('guest', function($g) use ($search) {
                    $g->where('full_name', 'like', "%{$search}%")
                      ->orWhereHas('guestAuth', function($ga) use ($search) {
                          $ga->where('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('phones', function($p) use ($search) {
                          $p->where('phone_number', 'like', "%{$search}%");
                      });
                })
                ->orWhereHas('transactions', function($t) use ($search) {
                    $t->where('payment_reference', 'like', "%{$search}%");
                });
            });
        }

        // 2. Filter by Status
        if ($status = $request->input('status')) {
            $query->where('booking_status', $status);
        }

        // 3. Filter by Date Range
        $dateType = $request->input('date_type', 'check_in_date');
        $validDateTypes = ['check_in_date', 'check_out_date', 'created_at'];
        if (!in_array($dateType, $validDateTypes)) {
            $dateType = 'check_in_date';
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate("bookings.{$dateType}", '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate("bookings.{$dateType}", '<=', $dateTo);
        }

        // 4. Filter by Booking Origin
        if ($bookingOrigin = $request->input('booking_origin')) {
            $query->where('booking_origin', $bookingOrigin);
        }

        // 5. Sorting
        $sort = $request->input('sort', 'latest_booking');
        match ($sort) {
            'earliest_booking' => $query->orderBy('bookings.id', 'asc'),
            'check_in_asc'     => $query->orderBy('bookings.check_in_date', 'asc'),
            'check_in_desc'    => $query->orderBy('bookings.check_in_date', 'desc'),
            'check_out_asc'    => $query->orderBy('bookings.check_out_date', 'asc'),
            'check_out_desc'   => $query->orderBy('bookings.check_out_date', 'desc'),
            'guest_asc'        => $query->join('guests', 'bookings.guest_id', '=', 'guests.id')->orderBy('guests.full_name', 'asc')->select('bookings.*'),
            'guest_desc'       => $query->join('guests', 'bookings.guest_id', '=', 'guests.id')->orderBy('guests.full_name', 'desc')->select('bookings.*'),
            'price_high'       => $query->orderBy('bookings.total_price', 'desc'),
            'price_low'        => $query->orderBy('bookings.total_price', 'asc'),
            default            => $query->orderBy('bookings.id', 'desc'),
        };

        $reportPrefix = match($dateType) {
            'check_in_date' => 'arrivals_report',
            'check_out_date' => 'departures_report',
            'created_at' => 'sales_report',
            default => 'bookings_report',
        };

        $filename = $reportPrefix;
        if ($dateFrom && $dateTo) {
            $start = \Carbon\Carbon::parse($dateFrom);
            $end = \Carbon\Carbon::parse($dateTo);
            
            if ($start->isSameDay($end)) {
                $filename .= '_daily_' . $start->format('Y-m-d');
            } elseif ($start->copy()->startOfMonth()->isSameDay($start) && $end->copy()->endOfMonth()->isSameDay($end) && $start->isSameMonth($end)) {
                $filename .= '_monthly_' . $start->format('M_Y');
            } elseif ($start->copy()->startOfYear()->isSameDay($start) && $end->copy()->endOfYear()->isSameDay($end) && $start->isSameYear($end)) {
                $filename .= '_yearly_' . $start->format('Y');
            } else {
                $filename .= '_from_' . $start->format('Y-m-d') . '_to_' . $end->format('Y-m-d');
            }
        } elseif ($dateFrom) {
            $filename .= '_from_' . \Carbon\Carbon::parse($dateFrom)->format('Y-m-d');
        } elseif ($dateTo) {
            $filename .= '_until_' . \Carbon\Carbon::parse($dateTo)->format('Y-m-d');
        } else {
            $filename .= '_all_time';
        }

        $filename .= '_' . now()->format('His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\BookingsExport($query),
            $filename
        );
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

        DB::transaction(function () use ($booking) {
            // Release any physically assigned rooms back to available status.
            foreach ($booking->bookingRooms()->with('bookingRooms.room')->get() as $bookingRoom) {
                if ($bookingRoom->room) {
                    $bookingRoom->room->update(['current_status' => 'available']);
                }
            }

            $booking->update(['booking_status' => Booking::STATUS_CANCELLED]);
        });

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

        // Only transactions that were explicitly flagged as refund_pending are eligible.
        // Non-refundable cancellations leave transactions as 'partial' and must not be touched here.
        $pendingTransactions = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_REFUND_PENDING)
            ->get();

        if ($pendingTransactions->isEmpty()) {
            return back()->with('error', 'No refund-pending payment found on this booking — either this was a non-refundable cancellation, or it has already been refunded.');
        }

        $alreadyRefunded = $booking->transactions()
            ->where('payment_status', Transaction::STATUS_REFUNDED)
            ->exists();

        if ($alreadyRefunded) {
            return back()->with('error', "Booking {$booking->referenceNumber()} has already been marked as refunded.");
        }

        DB::transaction(function () use ($pendingTransactions) {
            foreach ($pendingTransactions as $transaction) {
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
