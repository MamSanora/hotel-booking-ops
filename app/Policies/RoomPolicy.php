<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\GuestAuth;
use App\Models\Room;
use App\Models\Staff;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * RoomPolicy
 *
 * Defines who may manage Room records.
 *
 * Public room browsing (GET /rooms, GET /rooms/{room}) is unguarded —
 * anyone can view the room catalogue. This policy therefore only gates
 * the admin-panel management actions (create, update, delete).
 *
 * Registration:
 *   Gate::policy(Room::class, RoomPolicy::class);
 *   (Done in AppServiceProvider::boot)
 *
 * Usage in admin controller:
 *   Gate::forUser(Auth::guard('admin')->user())->authorize('create', Room::class);
 *   Gate::forUser(Auth::guard('admin')->user())->authorize('update', $room);
 *   Gate::forUser(Auth::guard('admin')->user())->authorize('delete', $room);
 */
class RoomPolicy
{
    /**
     * View the admin room listing.
     * Admins and staff may access the admin rooms panel.
     * Guests browse rooms via the public route (no policy gate required).
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $user instanceof Admin
            || $user instanceof Staff;
    }

    /**
     * View a single room's admin detail/edit page.
     */
    public function view(Authenticatable $user, Room $room): bool
    {
        return $user instanceof Admin
            || $user instanceof Staff;
    }

    /**
     * Create a new room.
     * Restricted to admins only — staff are read-only on room data.
     */
    public function create(Authenticatable $user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Update an existing room (type, price, capacity, status, description).
     * Restricted to admins only.
     */
    public function update(Authenticatable $user, Room $room): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Delete a room.
     * Restricted to admins only.
     *
     * A room may not be deleted while it has active bookings
     * (status 'pending', 'booked', or 'checked_in').
     * This check prevents orphaning active reservations.
     */
    public function delete(Authenticatable $user, Room $room): bool
    {
        if (! ($user instanceof Admin)) {
            return false;
        }

        // Block deletion if any active booking references this room.
        $hasActiveBooking = $room->bookings()
            ->whereIn('booking_status', ['pending', 'booked', 'checked_in'])
            ->exists();

        return ! $hasActiveBooking;
    }
}
