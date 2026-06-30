<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * GuestAuth Model
 *
 * The authenticatable model for the 'web' guard. Holds login credentials
 * (email + passwordhash) for guests who register an online account.
 *
 * This model intentionally separates authentication from profile data.
 * The guest's profile (name, gender, nationality) lives in the `guests`
 * table and is accessed via the guest() relationship.
 *
 * Implements MustVerifyEmail to enable the existing email verification flow.
 *
 * @property int         $id
 * @property int         $guest_id
 * @property string      $email
 * @property string      $passwordhash
 * @property string|null $email_verified_at
 */
class GuestAuth extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;

    protected $guard = 'web';

    protected $table = 'guest_auths';

    protected $fillable = [
        'guest_id',
        'email',
        'passwordhash',
        'email_verified_at',
    ];

    protected $hidden = [
        'passwordhash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // Auto-hash on assignment: $guestAuth->passwordhash = 'plain';
            'passwordhash'      => 'hashed',
        ];
    }

    // ── Auth Contract Overrides ────────────────────────────────────────────

    /**
     * DO NOT override getAuthIdentifierName() here.
     *
     * The session guard stores getAuthIdentifier() in the session and later
     * calls retrieveById($identifier) to reload the user. That method runs
     * Model::find($identifier), which looks up by PRIMARY KEY.
     *
     * If getAuthIdentifierName() returned 'email', the session would store
     * the email string, and find('email@...') would query WHERE id = 'email@...'
     * — which always returns null, causing an infinite login redirect loop.
     *
     * The login field (email) is specified separately via the credentials
     * array passed to Auth::attempt(['email' => ..., 'password' => ...]).
     * retrieveByCredentials() uses those keys for the WHERE clause independently.
     */

    /**
     * The column holding the bcrypt hash. Required by Laravel 12 contract.
     */
    public function getAuthPasswordName(): string
    {
        return 'passwordhash';
    }

    /**
     * Returns the hashed password value for credential verification.
     */
    public function getAuthPassword(): string
    {
        return $this->passwordhash;
    }

    /**
     * Returns the email used for password reset token lookup.
     * The `password_reset_tokens` table is keyed by email.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->email;
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The guest profile associated with these credentials.
     * Use this to access full_name, gender, nationality, etc.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    // ── Convenience Helpers ────────────────────────────────────────────────

    /**
     * Shortcut to the guest's full name for use in views and notifications.
     */
    public function getFullName(): string
    {
        return $this->guest?->full_name ?? 'Guest';
    }

    /**
     * Proxies bookings through the guest relationship so controllers can
     * call Auth::user()->bookings() consistently.
     */
    public function bookings()
    {
        return $this->guest?->bookings() ?? collect();
    }

    public function isGuest(): bool
    {
        return true;
    }

    public function isAdmin(): bool
    {
        return false;
    }

    public function isStaff(): bool
    {
        return false;
    }

    public function roleName(): string
    {
        return 'guest';
    }

    public function dashboardUrl(): string
    {
        return '/guest/dashboard';
    }
}
