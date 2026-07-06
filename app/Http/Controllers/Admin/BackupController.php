<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;

/**
 * BackupController
 *
 * Handles manually triggered database backups from the admin dashboard.
 * Restricted to authenticated admins only (enforced via route middleware).
 *
 * WHY WE USE Process INSTEAD OF Artisan::call():
 * ─────────────────────────────────────────────────────────────────────────
 * On Windows, `mysqldump` requires a working Winsock socket layer to connect
 * to MySQL via TCP. When `Artisan::call()` is used inside an HTTP request
 * served by `php artisan serve`, the mysqldump subprocess inherits the web
 * server's process environment, which has a broken Winsock context (error
 * 10106: WSAEPROVIDERFAILEDINIT). Running the backup via a fresh PHP process
 * (Symfony\Process) gives mysqldump the same clean environment as a terminal
 * invocation, resolving the socket error entirely.
 * ─────────────────────────────────────────────────────────────────────────
 *
 * Rate-limiting: one manual backup per admin per hour, enforced via cache.
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
     * Trigger a manual backup by spawning a fresh PHP process.
     * Only backs up the database (--only-db) to keep it fast and lightweight.
     */
    public function run(): RedirectResponse
    {
        $adminId  = Auth::guard('admin')->id();
        $cacheKey = "admin_manual_backup_{$adminId}";

        // ── Rate limit: block if a backup was triggered within the last hour ──
        if (Cache::has($cacheKey)) {
            // The cache value holds the Unix timestamp at which the lock expires.
            $expiresAt   = Cache::get($cacheKey, now()->timestamp);
            $secondsLeft = max(0, $expiresAt - now()->timestamp);
            $minutesLeft = (int) ceil($secondsLeft / 60);

            return redirect()
                ->route('admin.dashboard')
                ->with('backup_error', "A manual backup was already triggered recently. Please wait {$minutesLeft} more minute(s) before trying again.");
        }

        try {
            // Build an explicit environment for the subprocess.
            // Passing null would strip variables like TEMP/TMP from the web server
            // context, causing PHP's tmpfile() to fail (return false) inside
            // Spatie's credentials-file writer. We merge in the current env and
            // guarantee TEMP/TMP point to a writable directory.
            $tempDir = sys_get_temp_dir();
            $env     = array_merge(
                array_filter(getenv()),   // current process env (PHP array, no false values)
                [
                    'TEMP' => $tempDir,
                    'TMP'  => $tempDir,
                    'APP_ENV' => app()->environment(),
                ]
            );

            // Spawn a completely fresh PHP process so mysqldump inherits a clean
            // Windows socket environment (not the degraded web server context).
            $process = new Process(
                [PHP_BINARY, base_path('artisan'), 'backup:run', '--only-db'],
                base_path(),    // working directory
                $env,           // explicit env with guaranteed TEMP/TMP
                null,           // no stdin
                300             // 5-minute timeout
            );

            $process->run();

            if (! $process->isSuccessful()) {
                $errorOutput = trim($process->getErrorOutput() ?: $process->getOutput());
                return redirect()
                    ->route('admin.dashboard')
                    ->with('backup_error', 'Backup failed. Details: ' . $errorOutput);
            }

            // Store the expiry timestamp so any cache driver can calculate
            // remaining seconds without needing getTimeToLive().
            Cache::put($cacheKey, now()->addSeconds(self::COOLDOWN_SECONDS)->timestamp, self::COOLDOWN_SECONDS);

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
