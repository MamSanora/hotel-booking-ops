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
        if (! Auth::guard('staff')->check() && ! Auth::guard('admin')->check()) {
            return redirect()->route('reception.login')
                ->with('error', 'Please log in to access the reception panel.');
        }

        return $next($request);
    }
}
