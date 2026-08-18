<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Booking;
use App\Models\Transaction;

class CleanupAbandonedBookingsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     * This ensures the cleanup query adds ZERO latency to the user's page load.
     */
    public function terminate(Request $request, Response $response): void
    {
        $abandonedBookings = Booking::where('booking_status', Booking::STATUS_PENDING)
            ->where('created_at', '<=', now()->subMinutes(5))
            ->pluck('id');

        if ($abandonedBookings->isNotEmpty()) {
            Booking::whereIn('id', $abandonedBookings)
                ->update(['booking_status' => Booking::STATUS_ABANDONED]);

            Transaction::whereIn('booking_id', $abandonedBookings)
                ->where('payment_status', Transaction::STATUS_PENDING)
                ->update(['payment_status' => Transaction::STATUS_FAILED]);
        }

        // 2. Clear any expired payment locks on transactions (5-minute window)
        Transaction::clearExpiredLocks();
    }
}
