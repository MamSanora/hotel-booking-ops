<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Guest Model
 *
 * Represents a hotel guest's profile. This model is intentionally NOT
 * authenticatable — it holds profile data only. Authentication (email +
 * passwordhash) is managed by GuestAuth, and OAuth by AuthMethod.
 *
 * This design means a guest can exist in the system without a login account,
 * which supports walk-in, phone, and proxy bookings by receptionists.
 *
 * @property int         $id
 * @property string      $full_name
 * @property string|null $gender       'male'|'female'|'other'|'prefer_not_to_say'
 * @property string|null $nationality
 */
class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'gender',
        'nationality',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The guest's login credentials (exists only for registered guests).
     * Walk-in and phone-booking guests will have no guestAuth record.
     */
    public function guestAuth(): HasOne
    {
        return $this->hasOne(GuestAuth::class);
    }

    /**
     * OAuth / social login providers linked to this guest.
     */
    public function authMethods(): HasMany
    {
        return $this->hasMany(AuthMethod::class);
    }

    /**
     * Phone numbers associated with this guest.
     */
    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * All bookings made by or for this guest.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ── Computed Helpers ───────────────────────────────────────────────────

    /**
     * Whether this guest has a registered online account.
     */
    public function hasAccount(): bool
    {
        return $this->guestAuth()->exists();
    }

    /**
     * Returns the guest's first phone number, or null if none on record.
     */
    public function primaryPhone(): ?string
    {
        return $this->phones->first()?->phone_number;
    }

    /**
     * Human-readable gender label for display in views and reports.
     */
    public function displayGender(): string
    {
        return match ($this->gender) {
            'male'             => 'Male',
            'female'           => 'Female',
            'other'            => 'Other',
            'prefer_not_to_say' => 'Prefer not to say',
            default            => '—',
        };
    }

    /**
     * Active booking (checked-in status) for this guest, if any.
     */
    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->where('booking_status', 'checked-in')
            ->latest();
    }
}
