<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

/**
 * BackupController
 *
 * Handles manually triggered database backups from the admin dashboard.
 * Restricted to authenticated admins only (enforced via route middleware).
 *
 * Rate-limiting: one manual backup per admin per hour, enforced via cache.
 * This prevents the owner from accidentally overloading the server by
 * clicking the button repeatedly.
 *
 * Route: POST /admin/backup/run  → admin.backup.run
 */
class BackupController extends Controller
{
    /**
     * The number of seconds to lock out repeated backup requests.
     * 3600 = 1 hour.
     */
    private const COOLDOWN_SECONDS = 3600;

    /**
     * Trigger a manual backup via the Spatie backup Artisan command.
     * Only backs up the database (--only-db) to keep it fast and lightweight
     * for an on-demand request. A full file + DB backup runs on schedule.
     */
    public function run(): RedirectResponse
    {
        $adminId  = Auth::guard('admin')->id();
        $cacheKey = "admin_manual_backup_{$adminId}";

        // ── Rate limit: block if a backup was triggered within the last hour ──
        if (Cache::has($cacheKey)) {
            $secondsLeft  = Cache::getTimeToLive($cacheKey) ?? self::COOLDOWN_SECONDS;
            $minutesLeft  = (int) ceil($secondsLeft / 60);

            return redirect()
                ->route('admin.dashboard')
                ->with('backup_error', "A manual backup was already triggered recently. Please wait {$minutesLeft} more minute(s) before trying again.");
        }

        try {
            // Run backup synchronously (database only for speed).
            Artisan::call('backup:run', ['--only-db' => true]);

            // Lock out further requests for 1 hour.
            Cache::put($cacheKey, true, self::COOLDOWN_SECONDS);

            return redirect()
                ->route('admin.dashboard')
                ->with('backup_success', 'Manual backup completed successfully. Your data is safe.');

        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('backup_error', 'Backup failed: ' . $e->getMessage());
        }
    }
}
