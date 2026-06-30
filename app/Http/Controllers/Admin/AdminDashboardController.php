<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\GuestAuth;
use App\Models\Room;
use App\Models\Transaction;

/**
 * AdminDashboardController
 *
 * Displays live hotel statistics on the admin dashboard:
 *   - Room availability summary
 *   - Booking lifecycle counts
 *   - Today's arrivals and departures
 *   - Revenue (monthly + 7-day chart)
 *   - Registered guest account count
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
        ));
    }
}
