<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuthCleaner Middleware
 *
 * Protects routes that are exclusively for staff with the 'cleaner' role.
 * Cleaners log in via the same staff guard as receptionists, but are
 * confined to the /cleaner/* route group.
 *
 * Admins are also permitted to view cleaner pages for oversight purposes.
 *
 * Usage in routes/web.php:
 *   Route::middleware('auth.cleaner')->group(function () { ... });
 */
class AuthCleaner
{
    public function handle(Request $request, Closure $next): Response
    {
        $staff = Auth::guard('staff')->user();
        $admin = Auth::guard('admin')->user();

        // Admins can access everything.
        if ($admin) {
            return $next($request);
        }

        // Must be a logged-in staff member with the cleaner role.
        if (! $staff && ! $admin) {
            return redirect()->route('staff.login')
                ->with('error', 'Please log in to access the cleaner panel.');
        }

        if (! $staff || ! $staff->isCleaner()) {
            return redirect()->route('staff.login')
                ->with('error', 'Access denied. This area is for cleaning staff only.');
        }

        return $next($request);
    }
}
