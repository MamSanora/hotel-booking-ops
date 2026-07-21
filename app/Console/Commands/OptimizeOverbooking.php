<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\RoomType;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * OptimizeOverbooking
 *
 * Implements a Stochastic Approximation heuristic (Talluri & van Ryzin, Ch. 4)
 * to self-tune each RoomType's overbooking_multiplier nightly.
 *
 * Must run AFTER app:process-night-audit so that no-shows have already been
 * recorded before this command reads them.
 *
 * Scheduling (routes/console.php):
 *   Schedule::command('app:optimize-overbooking')->dailyAt('02:05');
 *
 * Feedback logic per room type:
 *   - Punishment: any relocation yesterday → multiplier -= PENALTY_STEP
 *     (We over-predicted no-shows; dial back the risk.)
 *   - Reward:     any no-show yesterday, no relocation → multiplier += REWARD_STEP
 *     (We played it too safe; room(s) went to waste; take a little more risk.)
 *   - No signal:  neither happened → multiplier unchanged.
 *
 * Bounds: 1.00 ≤ multiplier ≤ MAX_MULTIPLIER
 */
class OptimizeOverbooking extends Command
{
    /** @var string */
    protected $signature = 'app:optimize-overbooking';

    /** @var string */
    protected $description = "Tune each room type's overbooking multiplier based on yesterday's no-shows and relocations.";

    // ── Tuning knobs ─────────────────────────────────────────────────────────

    /** Decrease multiplier by this amount when a guest was walked (relocated). */
    private const PENALTY_STEP = 0.05;

    /** Increase multiplier by this amount when rooms went empty due to no-shows. */
    private const REWARD_STEP = 0.01;

    /** Minimum multiplier (1.00 = no overbooking at all). */
    private const MIN_MULTIPLIER = 1.00;

    /** Maximum multiplier (1.50 = accept up to 50% more bookings than physical rooms). */
    private const MAX_MULTIPLIER = 1.50;

    // ─────────────────────────────────────────────────────────────────────────

    public function handle(): int
    {
        $this->info('Starting Overbooking Optimisation...');

        $yesterday = Carbon::yesterday()->toDateString();
        $roomTypes = RoomType::all();

        foreach ($roomTypes as $roomType) {
            // Fetch all bookings for this room type whose check-in was yesterday.
            // By now ProcessNightAudit has already converted missed check-ins to STATUS_NO_SHOW.
            $yesterdayBookings = Booking::whereHas(
                    'room',
                    fn ($q) => $q->where('room_type_id', $roomType->id)
                )
                ->whereDate('check_in_date', $yesterday)
                ->get();

            $relocations = $yesterdayBookings
                ->where('booking_status', Booking::STATUS_RELOCATED)
                ->count();

            $noShows = $yesterdayBookings
                ->where('booking_status', Booking::STATUS_NO_SHOW)
                ->count();

            $current  = (float) $roomType->overbooking_multiplier;
            $adjusted = $current;

            if ($relocations > 0) {
                // We walked at least one guest — overbooking was too aggressive.
                $adjusted -= self::PENALTY_STEP;
                $this->line(sprintf(
                    '  [%s] %d relocation(s) yesterday → penalty applied (%.2f → %.2f)',
                    $roomType->display_name, $relocations, $current, $adjusted
                ));
            } elseif ($noShows > 0) {
                // Rooms went unused — we played it too safe.
                $adjusted += self::REWARD_STEP;
                $this->line(sprintf(
                    '  [%s] %d no-show(s), 0 relocations → reward applied (%.2f → %.2f)',
                    $roomType->display_name, $noShows, $current, $adjusted
                ));
            } else {
                $this->line(sprintf(
                    '  [%s] No signal yesterday. Multiplier unchanged (%.2f).',
                    $roomType->display_name, $current
                ));
            }

            // Clamp within safe bounds and persist.
            $roomType->overbooking_multiplier = min(self::MAX_MULTIPLIER, max(self::MIN_MULTIPLIER, $adjusted));
            $roomType->save();
        }

        $this->info('Overbooking Optimisation completed.');

        return Command::SUCCESS;
    }
}
