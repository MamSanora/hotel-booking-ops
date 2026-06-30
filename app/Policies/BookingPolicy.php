<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\GuestAuth;
use App\Models\Staff;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * BookingPolicy
 *
 * Defines who may perform each action on a Booking record.
 *
 * This application uses three distinct authentication guards:
 *   - 'admin'  → Admin model   (hotel administrators)
 *   - 'staff'  → Staff model   (front-desk receptionists)
 *   - 'web'    → GuestAuth     (self-service hotel guests)
 *
 * Because the Gate may receive any of these user types, every method
 * uses instanceof checks rather than relying on a single guard.
 *
 * Registration:
 *   Gate::policy(Booking::class, BookingPolicy::class);
 *   (Done in AppServiceProvider::boot)
 *
 * Usage examples:
 *   // Guest controller (web guard — Auth::user() is GuestAuth):
 *   $this->authorize('view', $booking);
 *
 *   // Admin controller (admin guard — must pass user explicitly):
 *   Gate::forUser(Auth::guard('admin')->user())->authorize('approve', $booking);
 *
 *   // Staff controller:
 *   Gate::forUser(Auth::guard('staff')->user())->authorize('cancel', $booking);
 */
class BookingPolicy
{
    /**
     * View the index listing of all bookings.
     * Only admins and staff access the full booking list.
     * Guests see their own bookings via GuestDashboardController (no policy gate needed).
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin
            || $user instanceof Staff;
    }

    /**
     * View a single booking's details.
     * - Admins and staff: any booking.
     * - Guests: only their own booking (matched via guest_id on GuestAuth).
     */
    public function view(Authenticatable $user, Booking $booking): bool
    {
        if ($user instanceof Admin || $user instanceof Staff) {
            return true;
        }

        if ($user instanceof GuestAuth) {
            // GuestAuth has a guest_id FK; Booking also has guest_id.
            return $booking->guest_id === $user->guest_id;
        }

        return false;
    }

    /**
     * Cancel a booking.
     * - Admins/staff: any booking, any status.
     * - Guests: only their own booking, and only when canCancel() returns true
     *   (i.e. booking_status is 'pending' or 'booked').
     */
    public function cancel(Authenticatable $user, Booking $booking): bool
    {
        if ($user instanceof Admin || $user instanceof Staff) {
            return true;
        }

        if ($user instanceof GuestAuth) {
            return $booking->guest_id === $user->guest_id
                && $booking->canCancel();
        }

        return false;
    }

    /**
     * Approve (confirm) a booking.
     * Only admins may approve a booking — this triggers status → 'booked'.
     */
    public function approve(Authenticatable $user, Booking $booking): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Permanently delete a booking record.
     * Restricted to admins only.
     * Note: soft-deletes are not used; this is a hard delete.
     */
    public function forceDelete(Authenticatable $user, Booking $booking): bool
    {
        return $user instanceof Admin;
    }
}
