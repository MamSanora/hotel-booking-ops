<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        // Disable CSRF for external webhooks
        $middleware->validateCsrfTokens(except: [
            'payment/callback',
            'api/payment/callback', // Just in case it's ever moved to API routes
        ]);

        $middleware->alias([
            // ── Multi-Guard Auth Middleware ──────────────────────────────────
            // Each middleware protects routes for its respective user type
            // using a dedicated session guard.

            // Guests use the default 'auth' middleware (web guard → GuestAuth).
            // Admins must be authenticated via the dedicated 'admin' guard.
            'auth.admin' => \App\Http\Middleware\AuthAdmin::class,

            // Staff (receptionists) must be authenticated via the 'staff' guard.
            // Renamed from 'auth.receptionist' to match the renamed guard/table.
            'auth.staff' => \App\Http\Middleware\AuthStaff::class,
        ]);

        // When an unauthenticated guest visits a protected route (e.g. /guest/dashboard),
        // redirect them to the guest login page instead of the default /login.
        $middleware->redirectGuestsTo(
            fn (\Illuminate\Http\Request $request) => route('guest.login')
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
