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
            
            $this->info("Cleaned up {$abandonedBookings->count()} newly abandoned bookings.");
        }

        // Catch-all: Ensure ANY pending transaction related to an abandoned booking is marked as failed.
        // This handles both newly abandoned bookings and any historical orphaned ones.
        $failedTxCount = \App\Models\Transaction::where('payment_status', \App\Models\Transaction::STATUS_PENDING)
            ->whereHas('booking', function ($query) {
                $query->where('booking_status', \App\Models\Booking::STATUS_ABANDONED);
            })
            ->update(['payment_status' => \App\Models\Transaction::STATUS_FAILED]);

        if ($failedTxCount > 0) {
            $this->info("Failed {$failedTxCount} pending transactions linked to abandoned bookings.");
        }

        // Also clear any stale payment-page locks whose 5-minute window has expired.
        $clearedLocks = \App\Models\Transaction::clearExpiredLocks();
        if ($clearedLocks > 0) {
            $this->info("Cleared {$clearedLocks} expired payment lock(s).");
        }
    }
}
