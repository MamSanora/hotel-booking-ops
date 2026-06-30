<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Room;
use App\Policies\BookingPolicy;
use App\Policies\RoomPolicy;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider
 *
 * Main application service provider.
 * Handles authorization policy registration and global Gate hooks.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Policy registration and Gate hooks live here because Laravel 12 no longer
     * ships a separate AuthServiceProvider out of the box.
     */
    public function boot(): void
    {
        // ── Superadmin Bypass ───────────────────────────────────────────────
        //
        // If the authenticated user is an Admin with role 'superadmin', grant
        // every Gate ability unconditionally — before individual policy checks run.
        // Returning true short-circuits all remaining policy methods.
        // Returning null falls through to the policy normally.
        Gate::before(function (Authenticatable $user, string $ability): ?bool {
            if ($user instanceof Admin && $user->isSuperAdmin()) {
                return true;
            }

            return null; // continue to the policy
        });

        // ── Policy Registration ─────────────────────────────────────────────
        //
        // Maps Eloquent models to their corresponding Policy classes.
        // Laravel will also discover policies by convention, but explicit
        // registration avoids ambiguity in the multi-guard setup.
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Room::class,    RoomPolicy::class);

        // ── Simple Inline Abilities ─────────────────────────────────────────
        //
        // These boolean abilities are used in Blade via @can / @cannot
        // to conditionally show admin/staff-only UI elements without
        // referencing a specific model instance.

        // True for any authenticated Admin.
        Gate::define('manage-rooms', function (Authenticatable $user): bool {
            return $user instanceof Admin;
        });

        // True for admins AND staff (staff can view bookings but not approve).
        Gate::define('manage-bookings', function (Authenticatable $user): bool {
            return $user instanceof Admin || $user instanceof \App\Models\Staff;
        });
    }
}
