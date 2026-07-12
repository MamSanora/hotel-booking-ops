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
 * for guests who register an online account.
 *
 * Supports two registration paths:
 *   - Email path  : email + passwordhash (classic)
 *   - Phone path  : login_phone + passwordhash (new)
 *
 * A single guest_auths row may have:
 *   - email only        (email-registered user, no phone login)
 *   - login_phone only  (phone-registered user, no email login)
 *   - both              (user who added both after registration)
 *
 * OTP flow (mock — no real SMS API):
 *   otp_code and otp_expires_at are populated on phone registration.
 *   phone_verified_at is set after the guest enters the correct code.
 *
 * @property int         $id
 * @property int         $guest_id
 * @property string|null $email
 * @property string|null $login_phone
 * @property string      $passwordhash
 * @property string|null $email_verified_at
 * @property string|null $phone_verified_at
 * @property string|null $otp_code
 * @property string|null $otp_expires_at
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
        'login_phone',
        'passwordhash',
        'email_verified_at',
        'phone_verified_at',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = [
        'passwordhash',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'otp_expires_at'    => 'datetime',
            // Auto-hash on assignment: $guestAuth->passwordhash = 'plain';
            'passwordhash'      => 'hashed',
        ];
    }

    // ── Auth Contract Overrides ────────────────────────────────────────────

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
     * Phone-only users have no email — returns empty string as a safe fallback.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->email ?? '';
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The guest profile associated with these credentials.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    // ── Convenience Helpers ────────────────────────────────────────────────

    /**
     * Whether this is a phone-registered user (no email credential).
     */
    public function isPhoneUser(): bool
    {
        return is_null($this->email) && ! is_null($this->login_phone);
    }

    /**
     * Whether the OTP code is still valid (not expired).
     */
    public function isOtpValid(string $code): bool
    {
        return $this->otp_code === $code
            && $this->otp_expires_at
            && $this->otp_expires_at->isFuture();
    }

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
