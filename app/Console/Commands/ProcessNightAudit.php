<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class ProcessNightAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-night-audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the night audit to mark stale bookings as no-show or checked-out.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Night Audit...');

        // 1. No Shows: Guests who had a confirmed booking but never checked in
        //    (check_in_date is in the past and status is still pending/booked).
        $noShowCount = Booking::whereIn('booking_status', [
                Booking::STATUS_PENDING,
                Booking::STATUS_BOOKED,
            ])
            ->whereDate('check_in_date', '<', today())
            ->update(['booking_status' => Booking::STATUS_NO_SHOW]);

        $this->info("Marked {$noShowCount} booking(s) as No Show.");

        // 2. Auto Check-Out: Guests whose check-out date has passed but the
        //    receptionist forgot to manually check them out.
        $autoCheckOutCount = Booking::where('booking_status', Booking::STATUS_CHECKED_IN)
            ->whereDate('check_out_date', '<', today())
            ->update(['booking_status' => Booking::STATUS_CHECKED_OUT]);

        $this->info("Auto checked-out {$autoCheckOutCount} booking(s).");

        $this->info('Night Audit completed successfully.');

        return Command::SUCCESS;
    }
}
