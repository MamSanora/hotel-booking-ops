<?php

namespace App\Http\Controllers\Auth\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StaffLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Staff LoginController
 *
 * Handles front-desk staff authentication using the 'staff' guard.
 * Staff log in with a username + password (staff table, username + passwordhash).
 * Replaces the old Receptionist/LoginController (which used email + password
 * against the removed 'receptionists' table).
 *
 * Completely isolated from the 'web' (guest) and 'admin' guards.
 *
 * Login URL:  GET  /reception/login
 * Process:    POST /reception/login
 * Dashboard:  /reception/dashboard
 */
class LoginController extends Controller
{
    /**
     * Show the staff login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('staff')->check()) {
            return redirect()->route('reception.dashboard');
        }

        return view('auth.staff.login');
    }

    /**
     * Process the staff login form.
     * Authentication and rate-limiting handled by StaffLoginRequest.
     */
    public function login(StaffLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('reception.dashboard'));
    }

    /**
     * Log the staff member out and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('reception.login');
    }
}
