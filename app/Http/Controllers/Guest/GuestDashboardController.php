<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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

        // Upcoming: active or confirmed bookings ordered by check-in date.
        $upcomingBookings = Booking::with(['room'])
            ->where('guest_id', $guestId)
            ->whereNotIn('booking_status', [
                Booking::STATUS_CHECKED_OUT,
                Booking::STATUS_CANCELLED,
                Booking::STATUS_NO_SHOW,
            ])
            ->orderBy('check_in_date')
            ->get();

        // Past: completed or cancelled bookings, most recent first.
        $pastBookings = Booking::with(['room'])
            ->where('guest_id', $guestId)
            ->whereIn('booking_status', [
                Booking::STATUS_CHECKED_OUT,
                Booking::STATUS_CANCELLED,
            ])
            ->orderByDesc('check_out_date')
            ->limit(10)
            ->get();

        return view('guest.dashboard', compact('upcomingBookings', 'pastBookings'));
    }
}
