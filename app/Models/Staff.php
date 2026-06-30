<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Staff Model
 *
 * Represents a front-desk receptionist. Replaces the old Receptionist model.
 * Staff authenticate via the 'staff' guard using username and passwordhash.
 *
 * Each staff member is optionally linked to the admin who manages them.
 * Staff can handle bookings (walk-in proxy bookings) and room service requests.
 *
 * @property int    $id
 * @property string $full_name
 * @property string $role             'receptionist'
 * @property int    $managed_by_admin_id
 * @property string $username
 * @property string $passwordhash
 */
class Staff extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $guard = 'staff';

    protected $table = 'staff';

    protected $fillable = [
        'full_name',
        'role',
        'managed_by_admin_id',
        'username',
        'passwordhash',
    ];

    protected $hidden = [
        'passwordhash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => 'string',
        ];
    }

    // ── Auth Contract Overrides ────────────────────────────────────────────

    /**
     * DO NOT override getAuthIdentifierName() here — see Admin model for
     * the full explanation. Returning 'username' would break session reload
     * and cause an infinite login redirect loop.
     */

    /**
     * The column holding the hashed password value.
     */
    public function getAuthPasswordName(): string
    {
        return 'passwordhash';
    }

    /**
     * Returns the hashed password value for auth verification.
     */
    public function getAuthPassword(): string
    {
        return $this->passwordhash;
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The admin responsible for managing this staff member.
     */
    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'managed_by_admin_id');
    }

    /**
     * Bookings where this staff member acted as proxy for a guest.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'handled_by_staff_id');
    }

    /**
     * Room service requests assigned to or claimed by this staff member.
     */
    public function roomServices(): HasMany
    {
        return $this->hasMany(RoomService::class, 'handled_by_staff_id');
    }

    // ── Role Helpers ───────────────────────────────────────────────────────

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isAdmin(): bool
    {
        return false;
    }

    public function isGuest(): bool
    {
        return false;
    }

    public function roleName(): string
    {
        return $this->role;
    }

    public function dashboardUrl(): string
    {
        return '/reception/dashboard';
    }
}
