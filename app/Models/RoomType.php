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
 * @property int         $id
 * @property string      $slug            e.g. 'standard_room'
 * @property string      $display_name    e.g. 'Standard Room'
 * @property int|null    $size_sqm        Fixed room size in square metres.
 * @property int         $adult_capacity  Maximum adults this type accommodates.
 * @property int         $child_capacity  Maximum children (under 12) this type accommodates.
 * @property float       $price_per_night
 * @property string|null $description
 */
class RoomType extends Model
{
    use HasFactory;

    /**
     * Overbooking multiplier — stored per room-type in the database so that
     * the nightly OptimizeOverbooking command can tune each type independently
     * based on observed no-show and relocation rates.
     *
     * Default: 1.10 (10 physical rooms → 11 virtual slots).
     * Runtime bounds: clamped between 1.00 (no overbooking) and 1.50.
     *
     * @see database/migrations/2026_07_21_200654_add_overbooking_multiplier_to_room_types_table.php
     * @see app/Console/Commands/OptimizeOverbooking.php
     */
    // overbooking_multiplier — $this->overbooking_multiplier (float, from DB)

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
        'size_sqm',
        'adult_capacity',
        'child_capacity',
        'overbooking_multiplier',
        'price_per_night',
        'description',
        'images',
        'is_visible',
        'use_mam_sanora_qr',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night'        => 'decimal:2',
            'size_sqm'               => 'integer',
            'adult_capacity'         => 'integer',
            'child_capacity'         => 'integer',
            'overbooking_multiplier' => 'float',
            'is_visible'             => 'boolean',
            'use_mam_sanora_qr'      => 'boolean',
            'images'                 => 'array',
        ];
    }

    /**
     * Alias accessor so that `->name` resolves to `display_name`.
     * The DB column is `display_name`; keeping this accessor prevents
     * blank labels wherever old code uses `$roomType->name`.
     */
    public function getNameAttribute(): string
    {
        return $this->display_name ?? '';
    }



    /**
     * All physical rooms of this type.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the total maximum capacity of this room type.
     */
    public function maxCapacity(): int
    {
        return $this->adult_capacity + $this->child_capacity;
    }

    /**
     * UI Settings for the room type (e.g. chart colors).
     */
    public function uiSettings()
    {
        return $this->hasOne(RoomTypeSetting::class);
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
        // Don't protect if capacity is extremely low (< 3). 
        // A physical room count of 1 should be bookable on any tier.
        if ($virtualCapacity < 3) {
            return [
                Booking::TIER_FULL       => 0,
                Booking::TIER_DEPOSIT_50 => 0,
                Booking::TIER_DEPOSIT_20 => 0,
            ];
        }

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
     *   1. virtualCapacity  = floor(physicalRooms × overbooking_multiplier)
     *      e.g. 10 rooms × 1.10 = 11 virtual slots (default multiplier).
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
     * NOTE ON MULTI-ROOM BOOKINGS:
     * Virtual room (overbooking) logic intentionally does NOT apply to multi-room bookings.
     * Walking a single guest and their partner is manageable, but walking a group of
     * families who booked multiple rooms is a severe operational failure. Multi-room
     * bookings must be fulfilled strictly by physical availability.
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

        // Absolute virtual ceiling — multiplier is now per-type and self-tuning.
        $virtualCapacity = (int) floor($physicalCount * $this->overbooking_multiplier);

        // Nested booking limit for the requested tier.
        $bookingLimits    = $this->computeBookingLimits($virtualCapacity);
        $tierBookingLimit = $bookingLimits[$requestedTier] ?? $virtualCapacity;

        // Count ALL active room quantities for this type on the overlapping date range.
        // OVERSTAY FAILSAFE: A booking that is currently Checked In always blocks capacity
        // regardless of its check_out_date. This prevents double-booking rooms occupied by
        // guests who have overstayed their scheduled departure.
        $totalActiveBookings = (int) \App\Models\BookingRoom::where('room_type_id', $this->id)
            ->whereHas('booking', function ($q) use ($checkIn, $checkOut, $excludeBookingId) {
                $q->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING])
                  ->where(function ($date) use ($checkIn, $checkOut) {
                      $date
                          // Standard date-range overlap
                          ->where(fn ($s) => $s->where('check_in_date', '<', $checkOut)
                                              ->where('check_out_date', '>', $checkIn))
                          // Overstay: guest is checked in right now — always blocked
                          ->orWhere(fn ($s) => $s->where('booking_status', Booking::STATUS_CHECKED_IN)
                                               ->where('check_in_date', '<=', now()->toDateString()));
                  })
                  ->when($excludeBookingId, fn ($q2) => $q2->where('id', '!=', $excludeBookingId));
            })
            ->sum('quantity');

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
        int $requestedTier = Booking::TIER_FULL,
        ?string $bedType = null,
        ?string $viewPreference = null,
        ?string $floorPreference = null,
        array $excludeIds = [],
    ): ?Room {
        $activeStatuses = [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING];

        $scoreRoom = function(Room $room) use ($bedType, $viewPreference, $floorPreference) {
            $score = 0;
            if ($bedType && $room->bed_configuration === $bedType) $score++;
            if ($viewPreference && $room->view_type === $viewPreference) $score++;
            if ($floorPreference && (string)$room->floor === (string)$floorPreference) $score++;
            return $score;
        };

        // ── Pass 1: Find a completely free room, sorted by best preference match ──
        $freeRooms = $this->rooms()
            ->available()
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->whereDoesntHave('bookings', fn ($q) => $q
                ->whereIn('booking_status', $activeStatuses)
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
            )
            ->get()
            ->map(function ($room) use ($scoreRoom) {
                $room->pref_score = $scoreRoom($room);
                return $room;
            });

        if ($freeRooms->isNotEmpty()) {
            return $freeRooms->sortByDesc('pref_score')->first();
        }

        // ── Pass 2 (Overbooking buffer): Find least-loaded room, tie-broken by best preference match ──
        $overbookedRooms = $this->rooms()
            ->available()
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->withCount(['bookings as conflict_count' => fn ($q) => $q
                ->whereIn('booking_status', $activeStatuses)
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
            ])
            ->get()
            ->map(function ($room) use ($scoreRoom) {
                $room->pref_score = $scoreRoom($room);
                return $room;
            });

        if ($overbookedRooms->isNotEmpty()) {
            return $overbookedRooms->sortBy([
                ['conflict_count', 'asc'],
                ['pref_score', 'desc'],
            ])->first();
        }

        return null;
    }

    /**
     * Pick N distinct available physical rooms for a multi-room booking.
     *
     * Iterates the same preference-aware scoring logic as pickAvailableRoom(),
     * but collects $count unique rooms, excluding IDs already selected in the
     * same booking to prevent assigning the same room number twice.
     *
     * Returns a Collection<Room>. The collection may have fewer than $count
     * items if there are not enough non-maintenance rooms (overbooking buffer
     * case). The caller must handle this gracefully.
     *
     * @param  int  $count          How many rooms to pick.
     * @param  array<int>  $exclude Room IDs already assigned (to avoid duplicates).
     */
    public function pickAvailableRooms(
        string $checkIn,
        string $checkOut,
        int $count = 1,
        int $requestedTier = Booking::TIER_FULL,
        ?string $bedType = null,
        ?string $viewPreference = null,
        ?string $floorPreference = null,
        array $exclude = [],
    ): \Illuminate\Support\Collection {
        $picked = collect();

        for ($i = 0; $i < $count; $i++) {
            $alreadyPicked = $picked->pluck('id')->toArray();
            $allExcludes = array_merge($exclude, $alreadyPicked);

            $room = $this->pickAvailableRoom(
                $checkIn,
                $checkOut,
                $requestedTier,
                $bedType,
                $viewPreference,
                $floorPreference,
                $allExcludes
            );

            // If no room was found at all (all slots exhausted), stop.
            if (!$room) {
                break;
            }

            $picked->push($room);
        }

        return $picked;
    }


    /**
     * Compute remaining available physical rooms count for a given date range (or today if null).
     */
    public function getAvailableCount(?string $checkIn = null, ?string $checkOut = null): int
    {
        $physicalCount = $this->rooms()->where('current_status', '!=', 'maintenance')->count();

        $checkIn  = $checkIn ?: now()->toDateString();
        $checkOut = $checkOut ?: now()->addDay()->toDateString();

        $activeBookings = (int) \App\Models\BookingRoom::where('room_type_id', $this->id)
            ->whereHas('booking', function ($q) use ($checkIn, $checkOut) {
                $q->whereIn('booking_status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN, Booking::STATUS_PENDING])
                  ->where(function ($date) use ($checkIn, $checkOut) {
                      $date
                          // Standard date-range overlap
                          ->where(fn ($s) => $s->where('check_in_date', '<', $checkOut)
                                              ->where('check_out_date', '>', $checkIn))
                          // Overstay failsafe: physically checked-in guests always block the room
                          ->orWhere(fn ($s) => $s->where('booking_status', Booking::STATUS_CHECKED_IN)
                                               ->where('check_in_date', '<=', now()->toDateString()));
                  });
            })
            ->sum('quantity');

        $remaining = $physicalCount - $activeBookings;
        if ($this->hasAvailableVirtualCapacity($checkIn, $checkOut)) {
            // Intentionally clamp virtual remaining rooms to exactly 1.
            // This natively restricts frontend quantity dropdowns from allowing multi-room bookings
            // within the virtual buffer, ensuring groups/families are never walked.
            return max(1, $remaining);
        }

        return max(0, $remaining);
    }
}
