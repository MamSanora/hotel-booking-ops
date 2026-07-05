<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\GuestAuth;
use App\Models\Room;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * AdminDashboardController
 *
 * Displays live hotel statistics on the admin dashboard:
 *   - Room availability summary
 *   - Booking lifecycle counts
 *   - Today's arrivals and departures
 *   - Revenue (monthly + 7-day chart)
 *   - Registered guest account count
 *   - System backup status (last backup timestamp + health indicator)
 *
 * Route: GET /admin/dashboard
 */
class AdminDashboardController extends Controller
{
    public function index()
    {
        // ── Room Statistics ────────────────────────────────────────────────
        $totalRooms     = Room::count();
        $availableRooms = Room::available()->count();
        $occupiedRooms  = Room::occupied()->count();

        // ── Booking Statistics ────────────────────────────────────────────
        $activeBookings = Booking::active()->count();

        // Guests expected to check in today (status = booked, date = today)
        $todayArrivals = Booking::arrivingToday()->count();

        // Guests expected to check out today (status = checked-in, date = today)
        $todayDepartures = Booking::departingToday()->count();

        // ── Revenue ───────────────────────────────────────────────────────
        // Sum of all fully-paid transactions this calendar month
        $monthlyRevenue = Transaction::successful()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_paid');

        // ── Registered Guests ─────────────────────────────────────────────
        // Count guest_auths rows — each represents one registered online account
        $totalGuests = GuestAuth::count();

        // ── Chart 1: Revenue last 7 days (bar chart) ──────────────────────
        $revenueLast7Days = collect(range(6, 0))->map(function (int $daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'date'    => $date->format('M d'),
                'revenue' => Transaction::successful()
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('amount_paid'),
            ];
        });

        // ── Chart 2: Bookings by status (doughnut chart) ──────────────────
        $bookingsByStatus = Booking::selectRaw('booking_status, count(*) as count')
            ->groupBy('booking_status')
            ->pluck('count', 'booking_status')
            ->toArray();

        // ── Backup Status ─────────────────────────────────────────────────
        // Read the most recent backup ZIP from the dedicated 'backups' disk.
        // Spatie stores files under: {backup.name}/{timestamp}.zip
        // Possible values for $backupStatus: 'healthy' | 'outdated' | 'no_backup' | 'unknown'
        $backupStatus   = 'no_backup';
        $lastBackupTime = null;

        try {
            $disk    = Storage::disk('backups');
            $appName = config('backup.backup.name', config('app.name', 'Laravel'));
            $files   = $disk->files($appName);

            if (! empty($files)) {
                // Sort descending so the newest file is first.
                rsort($files);
                $lastModified   = $disk->lastModified($files[0]);
                // Explicitly apply the app timezone so the display is in local time
                // (UTC+7) rather than raw UTC — which would be 7 hours behind.
                $lastBackupTime = Carbon::createFromTimestampUTC($lastModified)
                    ->setTimezone(config('app.timezone'));

                // Healthy = backed up within the last 25 hours (1-hour buffer over
                // the standard nightly schedule). Outdated = older than that.
                $backupStatus = $lastBackupTime->diffInHours(now()) <= 25
                    ? 'healthy'
                    : 'outdated';
            }
        } catch (Throwable) {
            // Disk unavailable or misconfigured — surface as unknown.
            $backupStatus = 'unknown';
        }

        return view('admin.dashboard', compact(
            'totalRooms',
            'availableRooms',
            'occupiedRooms',
            'activeBookings',
            'todayArrivals',
            'todayDepartures',
            'monthlyRevenue',
            'totalGuests',
            'revenueLast7Days',
            'bookingsByStatus',
            'backupStatus',
            'lastBackupTime',
        ));
    }
}
