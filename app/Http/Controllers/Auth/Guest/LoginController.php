<?php

namespace App\Http\Controllers\Auth\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GuestLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Guest LoginController
 *
 * Handles hotel guest authentication using the 'web' guard.
 * Guests log in with email + password (credentials stored in guest_auths table).
 *
 * Login URL:    GET  /guest/login
 * Process:      POST /guest/login
 * Dashboard:    /guest/dashboard
 */
class LoginController extends Controller
{
    /**
     * Show the guest login form.
     * Captures an intended redirect URL from query string if present.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->has('redirect')) {
            session(['url.intended' => $request->redirect]);
        }

        if (Auth::guard('web')->check()) {
            return redirect()->route('guest.dashboard');
        }

        return view('auth.guest.login');
    }

    /**
     * Process the guest login form.
     * Authentication and rate-limiting are handled by GuestLoginRequest.
     */
    public function login(GuestLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('guest.dashboard'));
    }

    /**
     * Log the guest out and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('guest.login');
    }
}
