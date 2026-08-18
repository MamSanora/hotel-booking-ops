<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\GuestAuth;
use App\Models\Guest;
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

        // All rooms for the room status board modal
        $allRooms = Room::with(['roomType', 'activeBookings.guest'])
            ->orderBy('room_number')
            ->get();

        // ── Booking Statistics ────────────────────────────────────────────
        $activeBookings = Booking::active()->count();

        // Guests expected to check in today (status = booked, date = today)
        $todayArrivals = Booking::arrivingToday()->count();

        // Guests expected to check out today (status = checked-in, date = today)
        $todayDepartures = Booking::departingToday()->count();

        // ── Today's Arrivals List (with guest name + room) ─────────────────
        $arrivalsToday = Booking::arrivingToday()
            ->with(['guest', 'bookingRooms.roomType', 'bookingRooms.room'])
            ->orderBy('check_in_date')
            ->get();

        // ── Today's Departures List (with guest name + room) ───────────────
        $departuresToday = Booking::departingToday()
            ->with(['guest', 'bookingRooms.roomType', 'bookingRooms.room'])
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
        $unregisteredGuests = Guest::doesntHave('guestAuth')->count();

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
            'unregisteredGuests',
            'backupStatus',
            'lastBackupTime',
            'allRooms',

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

        if ($end->lt($start)) {
            $end = $start->copy()->endOfDay();
        }

        $diffDays   = (int) $start->diffInDays($end);
        $groupByDay = $diffDays <= 60;

        // ── Cross-Filtering Logic ────────────────────────────────────────
        $fGuestType   = request('booking_origin');
        $fNationality = request('nationality');
        $fRoomType    = request('room_type');

        $applyBookingFilters = function($query) use ($fGuestType, $fNationality, $fRoomType) {
            if ($fGuestType) $query->where('bookings.booking_origin', $fGuestType);
            if ($fNationality) $query->whereHas('guest', fn($q) => $q->where('nationality', $fNationality));
            if ($fRoomType) $query->whereHas('bookingRooms.roomType', fn($q) => $q->where('display_name', $fRoomType));
        };

        $applyTxnFilters = function($query) use ($fGuestType, $fNationality, $fRoomType) {
            if ($fGuestType || $fNationality || $fRoomType) {
                $query->whereHas('booking', function($bQuery) use ($fGuestType, $fNationality, $fRoomType) {
                    if ($fGuestType) $bQuery->where('booking_origin', $fGuestType);
                    if ($fNationality) $bQuery->whereHas('guest', fn($q) => $q->where('nationality', $fNationality));
                    if ($fRoomType) $bQuery->whereHas('bookingRooms.roomType', fn($q) => $q->where('display_name', $fRoomType));
                });
            }
        };

        // ── Time-series: Revenue ──────────────────────────────────────────
        $txnQueryDay = Transaction::successful()->whereBetween('transactions.created_at', [$start, $end]);
        $applyTxnFilters($txnQueryDay);

        if ($groupByDay) {
            $rawRevenue = (clone $txnQueryDay)
                ->selectRaw('DATE(transactions.created_at) as period_key, SUM(transactions.amount_paid) as total')
                ->groupByRaw('DATE(transactions.created_at)')
                ->pluck('total', 'period_key')
                ->toArray();

            $revenueSeries = [];
            $cur = $start->copy();
            while ($cur->lte($end)) {
                $key = $cur->format('Y-m-d');
                $revenueSeries[] = ['label' => $cur->format('M d'), 'value' => (float) ($rawRevenue[$key] ?? 0)];
                $cur->addDay();
            }
        } else {
            $rawRevenue = (clone $txnQueryDay)
                ->selectRaw('YEAR(transactions.created_at) as yr, MONTH(transactions.created_at) as mo, SUM(transactions.amount_paid) as total')
                ->groupByRaw('YEAR(transactions.created_at), MONTH(transactions.created_at)')
                ->get()
                ->mapWithKeys(fn ($r) => [sprintf('%04d-%02d', $r->yr, $r->mo) => $r->total])
                ->toArray();

            $revenueSeries = [];
            $cur = $start->copy()->startOfMonth();
            $endM = $end->copy()->startOfMonth();
            while ($cur->lte($endM)) {
                $key = $cur->format('Y-m');
                $revenueSeries[] = ['label' => $cur->format('M Y'), 'value' => (float) ($rawRevenue[$key] ?? 0)];
                $cur->addMonth();
            }
        }

        // ── Time-series: Booking Volume ───────────────────────────────────
        $bookQueryDay = Booking::whereBetween('bookings.created_at', [$start, $end]);
        $applyBookingFilters($bookQueryDay);

        if ($groupByDay) {
            $rawBookings = (clone $bookQueryDay)
                ->selectRaw('DATE(bookings.created_at) as period_key, COUNT(*) as total')
                ->groupByRaw('DATE(bookings.created_at)')
                ->pluck('total', 'period_key')
                ->toArray();

            $volumeSeries = [];
            $cur = $start->copy();
            while ($cur->lte($end)) {
                $key = $cur->format('Y-m-d');
                $volumeSeries[] = ['label' => $cur->format('M d'), 'value' => (int) ($rawBookings[$key] ?? 0)];
                $cur->addDay();
            }
        } else {
            $rawBookings = (clone $bookQueryDay)
                ->selectRaw('YEAR(bookings.created_at) as yr, MONTH(bookings.created_at) as mo, COUNT(*) as total')
                ->groupByRaw('YEAR(bookings.created_at), MONTH(bookings.created_at)')
                ->get()
                ->mapWithKeys(fn ($r) => [sprintf('%04d-%02d', $r->yr, $r->mo) => $r->total])
                ->toArray();

            $volumeSeries = [];
            $cur = $start->copy()->startOfMonth();
            $endM = $end->copy()->startOfMonth();
            while ($cur->lte($endM)) {
                $key = $cur->format('Y-m');
                $volumeSeries[] = ['label' => $cur->format('M Y'), 'value' => (int) ($rawBookings[$key] ?? 0)];
                $cur->addMonth();
            }
        }

        // ── Booking Status Distribution ───────────────────────────────────
        $statusQuery = Booking::whereBetween('bookings.created_at', [$start, $end]);
        $applyBookingFilters($statusQuery);
        $bookingStatuses = $statusQuery
            ->selectRaw('bookings.booking_status, COUNT(*) as cnt')
            ->groupBy('bookings.booking_status')
            ->orderByRaw('COUNT(*) DESC')
            ->get()
            ->map(fn ($r) => [
                'label' => Booking::STATUS_LABELS[$r->booking_status] ?? ucfirst($r->booking_status),
                'value' => (int) $r->cnt,
            ])
            ->values();

        // ── Revenue by Room Type ──────────────────────────────────────────
        $revTypeQuery = Transaction::successful()
            ->whereBetween('transactions.created_at', [$start, $end])
            ->join('bookings', 'bookings.id', '=', 'transactions.booking_id')
            ->join('booking_room', 'booking_room.booking_id', '=', 'bookings.id')
            ->join('rooms', 'rooms.id', '=', 'booking_room.room_id')
            ->join('room_types', 'room_types.id', '=', 'rooms.room_type_id')
            ->leftJoin('room_type_settings', 'room_type_settings.room_type_id', '=', 'room_types.id');
        
        $applyTxnFilters($revTypeQuery);

        $revenueByType = $revTypeQuery
            ->selectRaw('room_types.display_name as label, SUM(transactions.amount_paid) as value, COUNT(DISTINCT bookings.id) as booking_count, MAX(room_type_settings.chart_color) as color')
            ->groupBy('room_types.id', 'room_types.display_name')
            ->orderByRaw('SUM(transactions.amount_paid) DESC')
            ->get()
            ->map(fn ($r) => [
                'label'         => $r->label,
                'value'         => (float) $r->value,
                'booking_count' => (int) $r->booking_count,
                'color'         => $r->color ?? '#3b82f6',
            ])
            ->values();

        // ── NEW: Revenue by Nationality ───────────────────────────────────
        $revNatQuery = Transaction::successful()
            ->whereBetween('transactions.created_at', [$start, $end])
            ->join('bookings', 'bookings.id', '=', 'transactions.booking_id')
            ->join('guests', 'guests.id', '=', 'bookings.guest_id');
        
        $applyTxnFilters($revNatQuery);

        $revenueByNationality = $revNatQuery
            ->selectRaw('guests.nationality as label, SUM(transactions.amount_paid) as value, COUNT(DISTINCT bookings.id) as booking_count')
            ->groupBy('guests.nationality')
            ->orderByRaw('SUM(transactions.amount_paid) DESC')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'label'         => $r->label ?? 'Unknown',
                'value'         => (float) $r->value,
                'booking_count' => (int) $r->booking_count,
            ])
            ->values();

        // ── NEW: Customers by Booking Origin ──────────────────────────────────
        $bookingOriginQuery = Booking::whereBetween('bookings.created_at', [$start, $end]);
        $applyBookingFilters($bookingOriginQuery);

        $volumeByGuestType = $bookingOriginQuery
            ->selectRaw('bookings.booking_origin as label, COUNT(*) as value')
            ->groupBy('bookings.booking_origin')
            ->orderByRaw('COUNT(*) DESC')
            ->get()
            ->map(fn ($r) => [
                'label' => ucfirst($r->label ?? 'Unknown'),
                'value' => (int) $r->value,
            ])
            ->values();

        // ── Scalar KPI summary ────────────────────────────────────────────
        $kpiRevQuery = Transaction::successful()->whereBetween('transactions.created_at', [$start, $end]);
        $applyTxnFilters($kpiRevQuery);
        $totalRevenue = (float) $kpiRevQuery->sum('transactions.amount_paid');

        $kpiBookQuery = Booking::whereBetween('bookings.created_at', [$start, $end]);
        $applyBookingFilters($kpiBookQuery);
        $totalBookings = $kpiBookQuery->count();

        $kpiCompQuery = Booking::whereBetween('bookings.created_at', [$start, $end])
            ->whereIn('bookings.booking_status', [Booking::STATUS_CHECKED_OUT, Booking::STATUS_CHECKED_IN]);
        $applyBookingFilters($kpiCompQuery);
        $completedBookings = $kpiCompQuery->count();

        return response()->json([
            'grouping'             => $groupByDay ? 'day' : 'month',
            'period'               => [
                'start' => $start->format('Y-m-d'),
                'end'   => $end->format('Y-m-d'),
                'label' => $start->format('M d, Y') . ' – ' . $end->format('M d, Y'),
            ],
            'revenue'              => $revenueSeries,
            'bookingVolume'        => $volumeSeries,
            'bookingStatuses'      => $bookingStatuses,
            'revenueByType'        => $revenueByType,
            'revenueByNationality' => $revenueByNationality,
            'volumeByGuestType'    => $volumeByGuestType,
            'summary'              => [
                'total_revenue'      => $totalRevenue,
                'total_bookings'     => $totalBookings,
                'completed_bookings' => $completedBookings,
            ],
        ]);
    }
}
