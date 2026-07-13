<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Room Model
 *
 * Represents a physical hotel room. Room type-level attributes
 * (price_per_night, capacity, description) are now stored in the
 * `room_types` table and accessed via the `roomType()` relationship.
 * This eliminates the previous 3NF violation where those values were
 * duplicated across every room row.
 *
 * @property int         $id
 * @property string|null $room_number
 * @property int         $room_type_id
 * @property string      $current_status  'available' | 'occupied'
 *
 * @property-read RoomType $roomType
 */
class Room extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED  = 'occupied';

    protected $fillable = [
        'room_number',
        'room_type_id',
        'current_status',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The room category (type, price, capacity, description).
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

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
     * Check if this specific room is available for a date range.
     */
    public function isAvailableForDates(string $checkIn, string $checkOut, ?int $excludeBookingId = null): bool
    {
        if ($this->current_status !== self::STATUS_AVAILABLE) {
            return false;
        }

        return !$this->bookings()
            ->whereIn('booking_status', ['booked', 'checked-in'])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->exists();
    }

    /**
     * Returns the human-readable room type label, e.g. "Standard Twin".
     * Delegates to the RoomType model if loaded; falls back gracefully.
     */
    public function displayType(): string
    {
        return $this->roomType?->display_name ?? 'Unknown Type';
    }

    /**
     * Returns the formatted price string, e.g. "$35.00 / night".
     * Delegates to the RoomType model.
     */
    public function displayPrice(): string
    {
        return $this->roomType?->displayPrice() ?? 'Price not set';
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
