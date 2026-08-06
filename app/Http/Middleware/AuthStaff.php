<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuthStaff Middleware
 *
 * Protects routes that require an authenticated front-desk staff member
 * or an administrator. Uses the 'staff' or 'admin' guards.
 *
 * Replaces the old AuthReceptionist middleware, updated to use the
 * renamed 'staff' guard (was 'receptionist').
 *
 * Usage in routes/web.php:
 *   Route::middleware('auth.staff')->group(function () { ... });
 */
class AuthStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('staff')->user();
        $admin = Auth::guard('admin')->user();

        if (! $staff && ! $admin) {
            return redirect()->route('reception.login')
                ->with('error', 'Please log in to access the reception panel.');
        }

        // Cleaners have their own dashboard — block them from reception routes.
        if ($staff && $staff->isCleaner()) {
            return redirect('/cleaner/dashboard')
                ->with('error', 'Cleaning staff do not have access to the reception panel.');
        }

        return $next($request);
    }
}
