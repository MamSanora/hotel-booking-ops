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
 *   - Today's arrivals and departures (with guest name lists)
 *   - Revenue (monthly total KPI card)
 *   - Registered guest account count
 *   - Occupancy rate percentage
 *   - System backup status (last backup timestamp + health indicator)
 *
 * The financial/booking charts are now loaded dynamically via the
 * analytics() AJAX endpoint below, not server-side rendered.
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
        $occupancyRate  = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // ── Booking Statistics ────────────────────────────────────────────
        $activeBookings = Booking::active()->count();

        // Guests expected to check in today (status = booked, date = today)
        $todayArrivals = Booking::arrivingToday()->count();

        // Guests expected to check out today (status = checked-in, date = today)
        $todayDepartures = Booking::departingToday()->count();

        // ── Today's Arrivals List (with guest name + room) ─────────────────
        $arrivalsToday = Booking::arrivingToday()
            ->with(['guest', 'room.roomType'])
            ->orderBy('check_in_date')
            ->get();

        // ── Today's Departures List (with guest name + room) ───────────────
        $departuresToday = Booking::departingToday()
            ->with(['guest', 'room.roomType'])
            ->orderBy('check_out_date')
            ->get();

        // ── Revenue ───────────────────────────────────────────────────────
        // Sum of all fully-paid transactions this calendar month (for the KPI card)
        $monthlyRevenue = Transaction::successful()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_paid');

        // ── Registered Guests ─────────────────────────────────────────────
        $totalGuests = GuestAuth::count();

        // ── Backup Status ─────────────────────────────────────────────────
        $backupStatus   = 'no_backup';
        $lastBackupTime = null;

        try {
            $disk    = Storage::disk('backups');
            $appName = config('backup.backup.name', config('app.name', 'Laravel'));
            $files   = $disk->files($appName);

            if (! empty($files)) {
                rsort($files);
                $lastModified   = $disk->lastModified($files[0]);
                $lastBackupTime = Carbon::createFromTimestampUTC($lastModified)
                    ->setTimezone(config('app.timezone'));

                $backupStatus = $lastBackupTime->diffInHours(now()) <= 25
                    ? 'healthy'
                    : 'outdated';
            }
        } catch (Throwable) {
            $backupStatus = 'unknown';
        }

        return view('admin.dashboard', compact(
            'totalRooms',
            'availableRooms',
            'occupiedRooms',
            'occupancyRate',
            'activeBookings',
            'todayArrivals',
            'todayDepartures',
            'arrivalsToday',
            'departuresToday',
            'monthlyRevenue',
            'totalGuests',
            'backupStatus',
            'lastBackupTime',
        ));
    }

    /**
     * AJAX endpoint powering the dynamic analytics charts.
     *
     * Accepts:
     *   ?start_date=YYYY-MM-DD  (default: 6 days ago — gives a 7-day window)
     *   ?end_date=YYYY-MM-DD    (default: today)
     *
     * Grouping strategy (auto):
     *   ≤ 60 days  → group by DAY   (labels like "Jul 15")
     *   > 60 days  → group by MONTH (labels like "Jul 2026")
     *
     * Returns JSON:
     *   grouping        : 'day' | 'month'
     *   period          : { start, end, label }
     *   revenue         : [{ label, value }]          — time-series, paid txns only
     *   bookingVolume   : [{ label, value }]          — time-series, all bookings
     *   bookingStatuses : [{ label, value }]          — aggregated for period
     *   revenueByType   : [{ label, value, booking_count }] — by room type
     *   summary         : { total_revenue, total_bookings, completed_bookings }
     *
     * Route: GET /admin/dashboard/analytics
     */
    public function analytics()
    {
        $start = request('start_date')
            ? Carbon::parse(request('start_date'))->startOfDay()
            : now()->subDays(6)->startOfDay();

        $end = request('end_date')
            ? Carbon::parse(request('end_date'))->endOfDay()
            : now()->endOfDay();

        // Clamp: end must not be before start
        if ($end->lt($start)) {
            $end = $start->copy()->endOfDay();
        }

        $diffDays   = (int) $start->diffInDays($end);
        $groupByDay = $diffDays <= 60;

        // ── Helper: build a zero-filled map keyed by period ───────────────

        // ── Time-series: Revenue ──────────────────────────────────────────
        if ($groupByDay) {
            $rawRevenue = Transaction::successful()
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as period_key, SUM(amount_paid) as total')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at)')
                ->pluck('total', 'period_key')
                ->toArray();

            $revenueSeries     = [];
            $cur               = $start->copy();
            while ($cur->lte($end)) {
                $key             = $cur->format('Y-m-d');
                $revenueSeries[] = ['label' => $cur->format('M d'), 'value' => (float) ($rawRevenue[$key] ?? 0)];
                $cur->addDay();
            }
        } else {
            $rawRevenue = Transaction::successful()
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('YEAR(created_at) as yr, MONTH(created_at) as mo, SUM(amount_paid) as total')
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at), MONTH(created_at)')
                ->get()
                ->mapWithKeys(fn ($r) => [sprintf('%04d-%02d', $r->yr, $r->mo) => $r->total])
                ->toArray();

            $revenueSeries = [];
            $cur           = $start->copy()->startOfMonth();
            $endM          = $end->copy()->startOfMonth();
            while ($cur->lte($endM)) {
                $key             = $cur->format('Y-m');
                $revenueSeries[] = ['label' => $cur->format('M Y'), 'value' => (float) ($rawRevenue[$key] ?? 0)];
                $cur->addMonth();
            }
        }

        // ── Time-series: Booking Volume ───────────────────────────────────
        if ($groupByDay) {
            $rawBookings = Booking::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as period_key, COUNT(*) as total')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at)')
                ->pluck('total', 'period_key')
                ->toArray();

            $volumeSeries = [];
            $cur          = $start->copy();
            while ($cur->lte($end)) {
                $key          = $cur->format('Y-m-d');
                $volumeSeries[] = ['label' => $cur->format('M d'), 'value' => (int) ($rawBookings[$key] ?? 0)];
                $cur->addDay();
            }
        } else {
            $rawBookings = Booking::whereBetween('created_at', [$start, $end])
                ->selectRaw('YEAR(created_at) as yr, MONTH(created_at) as mo, COUNT(*) as total')
                ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                ->orderByRaw('YEAR(created_at), MONTH(created_at)')
                ->get()
                ->mapWithKeys(fn ($r) => [sprintf('%04d-%02d', $r->yr, $r->mo) => $r->total])
                ->toArray();

            $volumeSeries = [];
            $cur          = $start->copy()->startOfMonth();
            $endM         = $end->copy()->startOfMonth();
            while ($cur->lte($endM)) {
                $key          = $cur->format('Y-m');
                $volumeSeries[] = ['label' => $cur->format('M Y'), 'value' => (int) ($rawBookings[$key] ?? 0)];
                $cur->addMonth();
            }
        }

        // ── Booking Status Distribution (for the selected period) ─────────
        $bookingStatuses = Booking::whereBetween('created_at', [$start, $end])
            ->selectRaw('booking_status, COUNT(*) as cnt')
            ->groupBy('booking_status')
            ->orderByRaw('COUNT(*) DESC')
            ->get()
            ->map(fn ($r) => [
                'label' => Booking::STATUS_LABELS[$r->booking_status] ?? ucfirst($r->booking_status),
                'value' => (int) $r->cnt,
            ])
            ->values();

        // ── Revenue by Room Type (paid transactions in period) ────────────
        $revenueByType = Transaction::successful()
            ->whereBetween('transactions.created_at', [$start, $end])
            ->join('bookings',    'bookings.id',    '=', 'transactions.booking_id')
            ->join('rooms',       'rooms.id',       '=', 'bookings.room_id')
            ->join('room_types',  'room_types.id',  '=', 'rooms.room_type_id')
            ->selectRaw('room_types.display_name as label, SUM(transactions.amount_paid) as value, COUNT(DISTINCT bookings.id) as booking_count')
            ->groupBy('room_types.display_name')
            ->orderByRaw('SUM(transactions.amount_paid) DESC')
            ->get()
            ->map(fn ($r) => [
                'label'         => $r->label,
                'value'         => (float) $r->value,
                'booking_count' => (int) $r->booking_count,
            ])
            ->values();

        // ── Scalar KPI summary ────────────────────────────────────────────
        $totalRevenue = (float) Transaction::successful()
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount_paid');

        $totalBookings = Booking::whereBetween('created_at', [$start, $end])->count();

        $completedBookings = Booking::whereBetween('created_at', [$start, $end])
            ->whereIn('booking_status', [Booking::STATUS_CHECKED_OUT, Booking::STATUS_CHECKED_IN])
            ->count();

        return response()->json([
            'grouping'        => $groupByDay ? 'day' : 'month',
            'period'          => [
                'start' => $start->format('Y-m-d'),
                'end'   => $end->format('Y-m-d'),
                'label' => $start->format('M d, Y') . ' – ' . $end->format('M d, Y'),
            ],
            'revenue'         => $revenueSeries,
            'bookingVolume'   => $volumeSeries,
            'bookingStatuses' => $bookingStatuses,
            'revenueByType'   => $revenueByType,
            'summary'         => [
                'total_revenue'      => $totalRevenue,
                'total_bookings'     => $totalBookings,
                'completed_bookings' => $completedBookings,
            ],
        ]);
    }
}
