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
     * the system will allow up to floor(10 * 1.10) = 11 virtual slots.
     *
     * Adjust this constant to raise or lower the overbooking buffer.
     */
    public const OVERBOOKING_MULTIPLIER = 1.10;

    /**
     * Protection-level step fraction.
     *
     * Each tier below the top class has this fraction of virtual capacity
     * "protected" (reserved) so that lower tiers cannot fill those slots.
     *
     * 0.10 means: for every 10 virtual slots, 1 slot is reserved per tier step.
     * Example (10 physical rooms → 11 virtual, step = 1):
     *   TIER_100 booking limit = 11   (top class: full access)
     *   TIER_50  booking limit = 10   (1 slot protected for TIER_100)
     *   TIER_20  booking limit =  9   (2 slots protected for TIER_100 + TIER_50)
     *
     * Source: Talluri & van Ryzin, "The Theory and Practice of Revenue
     * Management" (2004), §2.1.1.2 — Nested Protection Levels.
     */
    public const PROTECTION_STEP_FRACTION = 0.10;

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
     * Compute nested protection levels per Talluri & van Ryzin (2004), §2.1.1.2.
     *
     * The protection level y_j for tier j is the number of virtual slots
     * to RESERVE exclusively for classes at tier j and HIGHER, preventing
     * lower-tier demand from consuming them.
     *
     * Nested ordering (required by the textbook):
     *   y_100 < y_50 < y_20
     *   i.e. protection grows as tier decreases (lower-tier → more protected above it).
     *
     * Heuristic (proportional to virtual capacity, one step per tier below top):
     *   step     = max(1, floor(virtualCapacity × PROTECTION_STEP_FRACTION))
     *   y_100    = 0        — top class: nothing is protected above it
     *   y_50     = step     — 1 step protected for TIER_100 guests only
     *   y_20     = step × 2 — 2 steps protected for TIER_100 + TIER_50 guests
     *
     * @param  int  $virtualCapacity  Total virtual slots (physical × multiplier).
     * @return array<int,int>  Map of tier => protection level
     */
    public function computeProtectionLevels(int $virtualCapacity): array
    {
        $step = max(1, (int) floor($virtualCapacity * self::PROTECTION_STEP_FRACTION));

        return [
            Booking::TIER_FULL       => 0,
            Booking::TIER_DEPOSIT_50 => $step,
            Booking::TIER_DEPOSIT_20 => $step * 2,
        ];
    }

    /**
     * Derive nested booking limits from protection levels.
     *
     * Booking limit for class j = virtualCapacity − y_j
     * (Talluri & van Ryzin §2.1.1.2: b_j = C − y_j)
     *
     * This is the maximum total number of bookings (across ALL tiers) that
     * will be accepted when a guest of tier j requests a room.
     *
     * @param  int  $virtualCapacity
     * @return array<int,int>  Map of tier => booking limit
     */
    public function computeBookingLimits(int $virtualCapacity): array
    {
        $protectionLevels = $this->computeProtectionLevels($virtualCapacity);

        return array_map(
            fn (int $y) => max(0, $virtualCapacity - $y),
            $protectionLevels
        );
    }

    /**
     * Nested-booking-limit availability check.
     *
     * Implements the Standard Nesting policy from Talluri & van Ryzin (2004),
     * "The Theory and Practice of Revenue Management", §2.1.1 and §2.1.1.3.
     *
     * Algorithm:
     *   1. virtualCapacity  = floor(physicalRooms × OVERBOOKING_MULTIPLIER)
     *      e.g. 10 rooms × 1.10 = 11 virtual slots.
     *
     *   2. Compute the nested booking limit for the requested tier.
     *      (See computeBookingLimits() for the formula and example.)
     *
     *   3. Count ALL active bookings for this room type on the overlapping
     *      date range, regardless of their tier.
     *
     *   4. Allow the booking if and only if:
     *        totalActiveBookings < bookingLimit[requestedTier]
     *
     * Why counting ALL bookings is correct (Standard Nesting, §2.1.1.3):
     *   In Standard Nesting every accepted booking consumes one unit of
     *   capacity regardless of class. The booking limit for each tier
     *   determines the total-units threshold. High-tier guests have a
     *   higher limit (more access); low-tier guests have a lower limit.
     *   The absolute ceiling across all tiers is always virtualCapacity,
     *   eliminating the infinite double-booking flaw of the previous
     *   implementation (which only counted same-or-higher-tier bookings).
     *
     * @param  string    $checkIn          Check-in date (Y-m-d)
     * @param  string    $checkOut         Check-out date (Y-m-d)
     * @param  int       $requestedTier    Payment tier: 20, 50, or 100
     * @param  int|null  $excludeBookingId Booking ID to exclude (e.g. re-checks)
     */
    public function hasAvailableVirtualCapacity(
        string $checkIn,
        string $checkOut,
        int $requestedTier = Booking::TIER_FULL,
        ?int $excludeBookingId = null
    ): bool {
        // Physical rooms of this type that are not in maintenance.
        $physicalCount = $this->rooms()->where('current_status', '!=', 'maintenance')->count();

        // Absolute virtual ceiling (overbooking buffer included).
        $virtualCapacity = (int) floor($physicalCount * self::OVERBOOKING_MULTIPLIER);

        // Nested booking limit for the requested tier.
        $bookingLimits    = $this->computeBookingLimits($virtualCapacity);
        $tierBookingLimit = $bookingLimits[$requestedTier] ?? $virtualCapacity;

        // Count ALL active bookings for this type on the overlapping date range.
        // (Standard nesting: each booking consumes capacity regardless of tier.)
        $totalActiveBookings = Booking::where(function ($q) {
                $q->whereHas('room', fn ($q2) => $q2->where('room_type_id', $this->id));
            })
            ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->count();

        return $totalActiveBookings < $tierBookingLimit;
    }

    /**
     * Pick the best available physical room for a new booking of this type.
     *
     * Since the nested booking limits in hasAvailableVirtualCapacity() already
     * enforce the tier priority policy at the type level, room assignment is
     * a purely physical optimisation: find the room with the fewest conflicting
     * bookings, preferring a completely empty room first.
     *
     * The tier of existing bookings is irrelevant here — each booking occupies
     * exactly one physical slot.
     *
     * When all physical rooms are already occupied (the overbooking buffer slot):
     * we return the least-loaded room so that front-desk staff can resolve
     * the overbooked slot at check-in, most likely via a no-show.
     *
     * @param  string  $checkIn
     * @param  string  $checkOut
     * @param  int     $requestedTier  Kept for API compatibility; unused internally.
     */
    public function pickAvailableRoom(
        string $checkIn,
        string $checkOut,
        int $requestedTier = Booking::TIER_FULL
    ): ?Room {
        $activeStatuses = [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING];

        // Prefer a completely empty room (no conflicting bookings at any tier).
        $emptyRoom = $this->rooms()
            ->available()
            ->whereDoesntHave('bookings', fn ($q) => $q
                ->whereIn('booking_status', $activeStatuses)
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
            )
            ->first();

        if ($emptyRoom) {
            return $emptyRoom;
        }

        // Overbooking buffer slot: every physical room has at least one booking.
        // Return the least-loaded room (fewest conflicts) — this gives the best
        // statistical chance of resolution via a no-show at check-in.
        // Front-desk staff handle physical re-assignment if needed.
        return $this->rooms()
            ->available()
            ->withCount(['bookings as conflict_count' => fn ($q) => $q
                ->whereIn('booking_status', $activeStatuses)
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
            ])
            ->orderBy('conflict_count', 'asc')
            ->first();
    }

    /**
     * Compute remaining available physical rooms count for a given date range (or today if null).
     */
    public function getAvailableCount(?string $checkIn = null, ?string $checkOut = null): int
    {
        $physicalCount = $this->rooms()->where('current_status', '!=', 'maintenance')->count();

        $checkIn  = $checkIn ?: now()->toDateString();
        $checkOut = $checkOut ?: now()->addDay()->toDateString();

        $activeBookings = Booking::where(function ($q) {
                $q->whereHas('room', fn ($q2) => $q2->where('room_type_id', $this->id));
            })
            ->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->count();

        $remaining = $physicalCount - $activeBookings;
        if ($this->hasAvailableVirtualCapacity($checkIn, $checkOut)) {
            return max(1, $remaining);
        }

        return max(0, $remaining);
    }
}
