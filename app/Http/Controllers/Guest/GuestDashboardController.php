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
        return view('guest.dashboard');
    }
}
