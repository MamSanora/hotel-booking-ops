<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Room Model
 *
 * Represents a physical hotel room. Replaces the old Room model which had
 * legacy columns (room_title, wifi, bed_type) and a separate RoomType FK.
 * Room type is now stored directly as an enum for simplicity.
 *
 * @property int         $id
 * @property string|null $room_number
 * @property string      $current_status  'available' | 'occupied'
 * @property string|null $room_type
 * @property float|null  $price_per_night
 * @property int|null    $capacity
 */
class Room extends Model
{
    use HasFactory;

    // ── Room Type Constants ────────────────────────────────────────────────

    /** Human-readable labels for each room type enum value. */
    public const ROOM_TYPES = [
        'standard_twin'   => 'Standard Twin',
        'standard_double' => 'Standard Double',
        'deluxe_double'   => 'Deluxe Double',
        'family_room'     => 'Family Room',
        'suite'           => 'Suite',
    ];

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED  = 'occupied';

    protected $fillable = [
        'room_number',
        'current_status',
        'room_type',
        'price_per_night',
        'capacity',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'capacity'        => 'integer',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * All bookings ever assigned to this room.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * The currently active booking (checked-in), if any.
     */
    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->where('booking_status', 'checked-in')
            ->latest();
    }

    /**
     * Audit log of administrative actions performed on this room.
     */
    public function roomManagements(): HasMany
    {
        return $this->hasMany(RoomManagement::class);
    }

    // ── Query Scopes ───────────────────────────────────────────────────────

    /**
     * Filter to only available rooms.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('current_status', self::STATUS_AVAILABLE);
    }

    /**
     * Filter to only occupied rooms.
     */
    public function scopeOccupied(Builder $query): Builder
    {
        return $query->where('current_status', self::STATUS_OCCUPIED);
    }

    /**
     * Filter rooms available for a given date range.
     * Excludes rooms with active or booked bookings overlapping the period.
     */
    public function scopeAvailableForDates(
        Builder $query,
        string $checkIn,
        string $checkOut,
        ?int $excludeBookingId = null
    ): Builder {
        return $query->where('current_status', self::STATUS_AVAILABLE)
            ->whereDoesntHave('bookings', function (Builder $q) use ($checkIn, $checkOut, $excludeBookingId) {
                $q->whereIn('booking_status', ['booked', 'checked-in'])
                    ->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn)
                    ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId));
            });
    }

    // ── Display Helpers ────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->current_status === self::STATUS_AVAILABLE;
    }

    public function isOccupied(): bool
    {
        return $this->current_status === self::STATUS_OCCUPIED;
    }

    /**
     * Returns the human-readable room type label, e.g. "Standard Twin".
     */
    public function displayType(): string
    {
        return self::ROOM_TYPES[$this->room_type] ?? ucfirst(str_replace('_', ' ', $this->room_type ?? ''));
    }

    /**
     * Returns the formatted price string, e.g. "$120.00 / night".
     */
    public function displayPrice(): string
    {
        return $this->price_per_night
            ? '$' . number_format((float) $this->price_per_night, 2) . ' / night'
            : 'Price not set';
    }

    /**
     * Returns a badge-friendly status label.
     */
    public function displayStatus(): string
    {
        return match ($this->current_status) {
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_OCCUPIED  => 'Occupied',
            default                => ucfirst($this->current_status),
        };
    }
}
