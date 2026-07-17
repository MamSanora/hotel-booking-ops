<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * GuestDashboardController
 *
 * Displays the authenticated guest's personal dashboard:
 *   - Upcoming / active bookings (pending, booked, checked-in)
 *   - Past bookings (checked-out, cancelled)
 *
 * Auth::user() returns a GuestAuth instance.
 * Bookings are accessed via Auth::user()->guest->bookings().
 *
 * Route: GET /guest/dashboard
 */
class GuestDashboardController extends Controller
{
    public function index(): View
    {
        // GuestAuth → Guest → bookings()
        $guestId = Auth::user()->guest_id;

        // Upcoming: active or confirmed bookings ordered by check-in date,
        // and check-out date is today or in the future.
        // Exclude all terminal/non-actionable statuses so they don't linger.
        $upcomingBookings = Booking::with(['room'])
            ->where('guest_id', $guestId)
            ->whereNotIn('booking_status', [
                Booking::STATUS_CHECKED_OUT,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_NO_SHOW,
                Booking::STATUS_RELOCATED,
                Booking::STATUS_SNATCHED,
            ])
            ->whereDate('check_out_date', '>=', today())
            ->orderBy('check_in_date')
            ->get();

        // Past: completed, cancelled, relocated, snatched, no-show — or any
        // booking where the check-out date has already passed (safety net for
        // statuses the Night Audit hasn't caught yet).
        $pastBookings = Booking::with(['room'])
            ->where('guest_id', $guestId)
            ->where(function ($query) {
                $query->whereIn('booking_status', [
                    Booking::STATUS_CHECKED_OUT,
                    Booking::STATUS_CANCELLED,
                    Booking::STATUS_NO_SHOW,
                    Booking::STATUS_RELOCATED,
                    Booking::STATUS_SNATCHED,
                ])->orWhereDate('check_out_date', '<', today());
            })
            ->orderByDesc('check_out_date')
            ->limit(10)
            ->get();

        return view('guest.dashboard', compact('upcomingBookings', 'pastBookings'));
    }
}
