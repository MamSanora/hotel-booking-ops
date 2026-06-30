<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Admin LoginController
 *
 * Handles hotel administrator authentication using the 'admin' guard.
 * Admins log in with a username + password (stored in the admins table
 * as username + passwordhash — no email required).
 *
 * Completely isolated from the 'web' (guest) and 'staff' guards.
 *
 * Login URL:  GET  /admin/login
 * Process:    POST /admin/login
 * Dashboard:  /admin/dashboard
 */
class LoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin.login');
    }

    /**
     * Process the admin login form.
     * Authentication and rate-limiting are handled by AdminLoginRequest.
     */
    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Log the admin out and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
