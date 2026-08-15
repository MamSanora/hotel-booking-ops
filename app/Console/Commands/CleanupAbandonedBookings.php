<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupAbandonedBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cleanup-abandoned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks pending bookings older than 5 minutes as abandoned to free up room inventory.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $abandonedBookings = \App\Models\Booking::where('booking_status', \App\Models\Booking::STATUS_PENDING)
            ->where('created_at', '<=', now()->subMinutes(5))
            ->pluck('id');

        if ($abandonedBookings->isNotEmpty()) {
            \App\Models\Booking::whereIn('id', $abandonedBookings)
                ->update(['booking_status' => \App\Models\Booking::STATUS_ABANDONED]);

            $cancelledTxCount = \App\Models\Transaction::whereIn('booking_id', $abandonedBookings)
                ->where('payment_status', \App\Models\Transaction::STATUS_PENDING)
                ->update(['payment_status' => \App\Models\Transaction::STATUS_CANCELLED]);

            $this->info("Cleaned up {$abandonedBookings->count()} abandoned bookings and cancelled {$cancelledTxCount} pending transactions.");
        }

        // Also clear any stale payment-page locks whose 5-minute window has expired.
        $clearedLocks = \App\Models\Transaction::clearExpiredLocks();
        if ($clearedLocks > 0) {
            $this->info("Cleared {$clearedLocks} expired payment lock(s).");
        }
    }
}
