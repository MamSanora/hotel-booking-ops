<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuthAdmin Middleware
 *
 * Protects routes that require an authenticated administrator.
 * Uses the 'admin' guard (admins table), completely separate from the
 * 'web' guard used by customers.
 *
 * Usage in routes/web.php:
 *   Route::middleware('auth.admin')->group(function () { ... });
 *
 * File: app/Http/Middleware/AuthAdmin.php
 */
class AuthAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if an admin is authenticated via the 'admin' guard
        if (!Auth::guard('admin')->check()) {
            // Not authenticated — redirect to admin login page
            return redirect()->route('admin.login')
                ->with('error', 'Please log in to access the admin panel.');
        }

        return $next($request);
    }
}
