<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

/**
 * SyncExchangeRate
 *
 * Artisan command that fetches the latest USD→KHR rate from the
 * Frankfurter API (NBC source) and persists it to the database.
 *
 * Usage:
 *   php artisan app:sync-exchange-rate
 *
 * This is run automatically by the scheduler (dailyAt 08:00) and can
 * also be triggered manually by an admin via the dashboard UI.
 */
class SyncExchangeRate extends Command
{
    protected $signature = 'app:sync-exchange-rate';

    protected $description = 'Fetch the latest USD→KHR exchange rate from NBC via Frankfurter and save it to the database.';

    public function handle(ExchangeRateService $service): int
    {
        $this->info('Syncing USD→KHR exchange rate…');

        try {
            $record = $service->fetchAndSave();

            $this->info(sprintf(
                '✓ Rate synced: 1 USD = %s KHR  (source: %s, dated: %s)',
                number_format($record->rate, 2),
                $record->source,
                $record->fetched_at->toDateString()
            ));

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('✗ Failed to sync exchange rate: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
