<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RoomType Model
 *
 * Represents a category of hotel room (e.g. Standard Twin, Deluxe Double).
 * Extracted from the `rooms` table to eliminate the 3NF violation where
 * capacity, price, and description were duplicated across every room row.
 *
 * @property int    $id
 * @property string $slug            e.g. 'standard_twin'
 * @property string $display_name    e.g. 'Standard Twin'
 * @property int    $capacity
 * @property float  $price_per_night
 * @property string|null $description
 */
class RoomType extends Model
{
    use HasFactory;

    /**
     * Overbooking multiplier: allows selling more bookings than there are
     * physical rooms of a given type, hedging against statistical no-shows.
     *
     * 1.10 means: if there are 10 physical rooms of this type,
     * the system will allow up to floor(10 * 1.10) = 11 bookings.
     *
     * Adjust this constant to raise or lower the overbooking aggression.
     */
    public const OVERBOOKING_MULTIPLIER = 1.10;

    protected $fillable = [
        'slug',
        'display_name',
        'capacity',
        'price_per_night',
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
     * All physical rooms of this type.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Returns the formatted price string, e.g. "$35.00 / night".
     */
    public function displayPrice(): string
    {
        return '$' . number_format((float) $this->price_per_night, 2) . ' / night';
    }

    /**
     * Predictive overbooking availability check.
     *
     * Determines whether this Room Type still has virtual capacity for a new
     * booking on the given dates, considering both overbooking and the payment
     * tier priority system.
     *
     * How it works:
     *   1. Virtual capacity = floor(physical_room_count * OVERBOOKING_MULTIPLIER)
     *      e.g. 10 rooms * 1.10 = 11 virtual slots.
     *   2. Active bookings at >= $requestedTier are counted for the date range.
     *   3. If active_bookings < virtual_capacity, the booking is allowed.
     *
     * Combined with payment tiers:
     *   - Tier 20 requested → counts any booking at tier 20, 50, or 100
     *   - Tier 50 requested → counts only bookings at tier 50 or 100
     *   - Tier 100 requested → counts only bookings at tier 100
     *
     * This means a 100%-tier guest can always find capacity as long as the
     * number of full-price bookings hasn't exceeded virtual capacity, even
     * if the type is "full" at 20%-tier level.
     *
     * @param  string    $checkIn        Check-in date (Y-m-d)
     * @param  string    $checkOut       Check-out date (Y-m-d)
     * @param  int       $requestedTier  Payment tier: 20, 50, or 100
     * @param  int|null  $excludeBookingId  Booking ID to exclude (e.g. re-checks)
     */
    public function hasAvailableVirtualCapacity(
        string $checkIn,
        string $checkOut,
        int $requestedTier = Booking::TIER_FULL,
        ?int $excludeBookingId = null
    ): bool {
        // How many physical rooms exist for this type?
        $physicalCount = $this->rooms()->where('current_status', '!=', 'maintenance')->count();

        // Apply the overbooking multiplier to get the virtual ceiling.
        $virtualCapacity = (int) floor($physicalCount * self::OVERBOOKING_MULTIPLIER);

        // Count active bookings for this type on the overlapping date range,
        // filtered by payment_tier >= requestedTier (tier priority logic).
        $activeBookings = Booking::whereHas('room', fn ($q) => $q->where('room_type_id', $this->id))
            ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->where('payment_tier', '>=', $requestedTier)
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->count();

        return $activeBookings < $virtualCapacity;
    }

    /**
     * Pick the best available physical room for a new booking of this type.
     *
     * Preference order:
     *   1. A room with NO active bookings on those dates at any tier (cleanest).
     *   2. A room that has only lower-tier bookings (the tier system allows this).
     *
     * Returns null if no physical room of this type can accommodate the booking
     * at the given tier (should not happen if hasAvailableVirtualCapacity() was
     * checked first, but handled gracefully).
     *
     * @param  string  $checkIn
     * @param  string  $checkOut
     * @param  int     $requestedTier
     */
    public function pickAvailableRoom(
        string $checkIn,
        string $checkOut,
        int $requestedTier = Booking::TIER_FULL
    ): ?Room {
        // Prefer a completely empty room (no conflicting bookings at any tier).
        $emptyRoom = $this->rooms()
            ->available()  // physical availability scope (not in maintenance)
            ->whereDoesntHave('bookings', fn ($q) => $q
                ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING])
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
            )
            ->first();

        if ($emptyRoom) {
            return $emptyRoom;
        }

        // Fall back: find a room that is available AT the requested tier
        // (has only lower-tier bookings on those dates — tier priority allows this).
        return $this->rooms()
            ->available()
            ->whereDoesntHave('bookings', fn ($q) => $q
                ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING])
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
                ->where('payment_tier', '>=', $requestedTier)
            )
            ->first();
    }
}
