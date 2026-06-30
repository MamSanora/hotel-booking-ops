<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Admin Model
 *
 * Represents a hotel administrator. Admins authenticate via the 'admin'
 * guard using a username and passwordhash (not email). Two role levels
 * exist: 'superadmin' has unrestricted access; 'admin' has standard access.
 *
 * @property int    $id
 * @property string $full_name
 * @property string $role          'superadmin' | 'admin'
 * @property string $username
 * @property string $passwordhash
 */
class Admin extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'full_name',
        'role',
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
            // Ensures $admin->role returns a clean string for comparison.
            'role' => 'string',
        ];
    }

    // ── Auth Contract Overrides ────────────────────────────────────────────

    /**
     * DO NOT override getAuthIdentifierName() here.
     *
     * The session guard stores this value and later calls retrieveById() to
     * reload the user — which queries by PRIMARY KEY (id). Returning 'username'
     * would cause the session to store the username string, and
     * Admin::find('admin') would run WHERE id = 'admin' → null → login loop.
     *
     * The login field (username) is handled separately via the credentials
     * array: Auth::guard('admin')->attempt(['username' => ..., 'password' => ...])
     * retrieveByCredentials() builds WHERE username = '...' from that array.
     */

    /**
     * The column that holds the hashed password.
     * Required by Laravel 12's Authenticatable contract.
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
     * Staff members managed by this admin.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'managed_by_admin_id');
    }

    /**
     * Room management audit log entries created by this admin.
     */
    public function roomManagements(): HasMany
    {
        return $this->hasMany(RoomManagement::class, 'managed_by_admin_id');
    }

    /**
     * Catalog items created by this admin.
     */
    public function itemsCatalogs(): HasMany
    {
        return $this->hasMany(ItemsCatalog::class, 'created_by_admin_id');
    }

    // ── Role Helpers ───────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return true;
    }

    public function isStaff(): bool
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
        return '/admin/dashboard';
    }
}
