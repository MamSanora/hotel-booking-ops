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
 * Handles front-desk and cleaning staff authentication using the 'staff' guard.
 * Staff log in with a username + password (staff table, username + passwordhash).
 *
 * Completely isolated from the 'web' (guest) and 'admin' guards.
 *
 * Login URL:  GET  /staff/login
 * Process:    POST /staff/login
 * Dashboard:  Dynamically routes to /reception/dashboard or /cleaner/dashboard
 */
class LoginController extends Controller
{
    /**
     * Show the staff login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('staff')->check()) {
            return redirect()->intended(Auth::guard('staff')->user()->dashboardUrl());
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

        return redirect()->intended(Auth::guard('staff')->user()->dashboardUrl());
    }

    /**
     * Log the staff member out and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
