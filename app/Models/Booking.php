<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Booking Model
 *
 * Core operational model tracking every room reservation. Supports both
 * self-service (online) bookings by registered guests and proxy bookings
 * created by receptionists for walk-in or phone guests.
 *
 * Status lifecycle:
 *   pending → booked → checked-in → checked-out
 *                    ↘ cancelled / no_show
 *
 * @property int $id
 * @property int|null $guest_id
 * @property int|null $room_id
 * @property int|null $handled_by_staff_id
 * @property string|null $check_in_date
 * @property string|null $check_out_date
 * @property int $number_of_stay_extension
 * @property float|null $total_price
 * @property string $booking_status
 * @property string|null $guest_type
 */
class Booking extends Model
{
    use HasFactory;

    // ── Status Constants ───────────────────────────────────────────────────

    public const STATUS_PENDING = 'pending';

    public const STATUS_BOOKED = 'booked';

    public const STATUS_CHECKED_IN = 'checked-in';

    public const STATUS_CHECKED_OUT = 'checked-out';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    public const STATUS_RELOCATED = 'relocated';

    /**
     * Snatched — set when two guests race to pay for the same tier on the
     * same room and dates simultaneously. The slower payer loses and their
     * booking is marked 'snatched' so staff can issue a refund and/or offer
     * an alternate room. Distinct from 'cancelled' (guest-initiated).
     */
    public const STATUS_SNATCHED = 'snatched';

    // ── Payment Tier Constants ─────────────────────────────────────────────

    /** Guest pays 20% upfront; remainder settled at check-in. */
    public const TIER_DEPOSIT_20 = 20;

    /** Guest pays 50% upfront; remainder settled at check-in. */
    public const TIER_DEPOSIT_50 = 50;

    /** Guest pays the full amount upfront. */
    public const TIER_FULL = 100;

    /** All valid payment tiers. */
    public const PAYMENT_TIERS = [self::TIER_DEPOSIT_20, self::TIER_DEPOSIT_50, self::TIER_FULL];

    /** Human-readable labels for each status value. */
    public const STATUS_LABELS = [
        'pending'     => 'Pending',
        'booked'      => 'Booked',
        'checked-in'  => 'Checked In',
        'checked-out' => 'Checked Out',
        'cancelled'   => 'Cancelled',
        'no_show'     => 'No Show',
        'relocated'   => 'Relocated',
        'snatched'    => 'Snatched',
    ];

    // ── Guest Type Constants ───────────────────────────────────────────────

    public const GUEST_TYPE_USER = 'user';

    public const GUEST_TYPE_WALKIN = 'walk-in';

    public const GUEST_TYPE_PHONE = 'phone';

    public const GUEST_TYPE_OTHER = 'other';

    protected $fillable = [
        'guest_id',
        'room_id',
        'handled_by_staff_id',
        'check_in_date',
        'check_out_date',
        'number_of_stay_extension',
        'total_price',
        'payment_tier',
        'booking_status',
        'guest_type',
        'special_requests',
        'relocated_to_booking_id',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date'            => 'date',
            'check_out_date'           => 'date',
            'total_price'              => 'decimal:2',
            'payment_tier'             => 'integer',
            'number_of_stay_extension' => 'integer',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The guest this booking belongs to.
     * Nullable — preserved as null if the guest account is deleted.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * The room assigned to this booking.
     * Fixed from the old model which incorrectly used hasOne instead of belongsTo.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * The staff member who handled this booking (walk-in / phone proxy).
     * Null for self-service online bookings.
     */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'handled_by_staff_id');
    }

    /**
     * Room service requests submitted during this booking's stay.
     */
    public function roomServices(): HasMany
    {
        return $this->hasMany(RoomService::class);
    }

    /**
     * All payment transactions for this booking (initial + extensions).
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * The booking this guest was relocated to (if status is 'relocated').
     */
    public function relocatedTo(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'relocated_to_booking_id');
    }

    /**
     * The most recent completed (full) transaction for this booking.
     */
    public function latestFullTransaction(): ?Transaction
    {
        return $this->transactions()
            ->where('payment_status', 'full')
            ->latest()
            ->first();
    }

    // ── Query Scopes ───────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('booking_status', self::STATUS_PENDING);
    }

    public function scopeBooked(Builder $query): Builder
    {
        return $query->where('booking_status', self::STATUS_BOOKED);
    }

    public function scopeCheckedIn(Builder $query): Builder
    {
        return $query->where('booking_status', self::STATUS_CHECKED_IN);
    }

    public function scopeCheckedOut(Builder $query): Builder
    {
        return $query->where('booking_status', self::STATUS_CHECKED_OUT);
    }

    /**
     * Active bookings — those that are booked or currently checked in.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('booking_status', [
            self::STATUS_BOOKED,
            self::STATUS_CHECKED_IN,
        ]);
    }

    /**
     * Bookings checking in today (arriving today exactly).
     */
    public function scopeArrivingToday(Builder $query): Builder
    {
        return $query->where('booking_status', self::STATUS_BOOKED)
            ->whereDate('check_in_date', today());
    }

    /**
     * All upcoming confirmed arrivals — today and beyond.
     * Used by the reception dashboard so staff can see the full arrivals pipeline.
     */
    public function scopeUpcomingArrivals(Builder $query): Builder
    {
        return $query->where('booking_status', self::STATUS_BOOKED)
            ->whereDate('check_in_date', '>=', today());
    }

    /**
     * Recent booking history — checked-out or cancelled bookings within the last 14 days.
     * Gives reception staff just enough context for lost-and-found, billing queries, etc.
     */
    public function scopeRecentHistory(Builder $query): Builder
    {
        return $query->whereIn('booking_status', [
                self::STATUS_CHECKED_OUT,
                self::STATUS_CANCELLED,
                self::STATUS_RELOCATED,
            ])
            ->where('updated_at', '>=', now()->subDays(14));
    }

    /**
     * Bookings checking out today.
     */
    public function scopeDepartingToday(Builder $query): Builder
    {
        return $query->where('booking_status', self::STATUS_CHECKED_IN)
            ->whereDate('check_out_date', today());
    }

    // ── Status Helpers ─────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->booking_status === self::STATUS_PENDING;
    }

    public function isBooked(): bool
    {
        return $this->booking_status === self::STATUS_BOOKED;
    }

    public function isCheckedIn(): bool
    {
        return $this->booking_status === self::STATUS_CHECKED_IN;
    }

    public function isCheckedOut(): bool
    {
        return $this->booking_status === self::STATUS_CHECKED_OUT;
    }

    public function isCancelled(): bool
    {
        return $this->booking_status === self::STATUS_CANCELLED;
    }

    public function isNoShow(): bool
    {
        return $this->booking_status === self::STATUS_NO_SHOW;
    }

    public function canCheckIn(): bool
    {
        return $this->booking_status === self::STATUS_BOOKED
            && $this->check_in_date->startOfDay()->lte(now()->startOfDay());
    }

    public function canCheckOut(): bool
    {
        return $this->booking_status === self::STATUS_CHECKED_IN;
    }

    public function canCancel(): bool
    {
        return in_array($this->booking_status, [
            self::STATUS_PENDING,
            self::STATUS_BOOKED,
        ]);
    }

    public function isRelocated(): bool
    {
        return $this->booking_status === self::STATUS_RELOCATED;
    }

    public function isSnatched(): bool
    {
        return $this->booking_status === self::STATUS_SNATCHED;
    }

    /**
     * The upfront amount due at the time of booking, based on payment_tier.
     * E.g. for a $200 total at 50% tier, this returns 100.00.
     */
    public function depositAmount(): float
    {
        return round((float) $this->total_price * ($this->payment_tier / 100), 2);
    }

    /**
     * The balance remaining to be settled at check-in.
     * Returns 0 for full (100%) tier bookings.
     */
    public function remainingBalance(): float
    {
        return round((float) $this->total_price - $this->depositAmount(), 2);
    }

    /**
     * The actual total amount paid so far, based on successful transactions.
     * This handles deposits, full payments, and stay extensions dynamically.
     */
    public function totalPaid(): float
    {
        return (float) $this->transactions()
            ->whereIn('payment_status', ['full', 'half'])
            ->sum('amount_paid');
    }

    /**
     * The actual remaining balance left to pay (Total Price - Total Paid).
     */
    public function balanceDue(): float
    {
        return round(max(0, (float) $this->total_price - $this->totalPaid()), 2);
    }

    /**
     * Policy: Free up to 24 hours before check-in (14:00).
     */
    public function isRefundable(): bool
    {
        if (! $this->check_in_date) {
            return false;
        }

        $checkInDateTime = Carbon::parse($this->check_in_date->format('Y-m-d') . ' 14:00:00');
        return now()->lessThanOrEqualTo($checkInDateTime->subDay());
    }

    // ── Display Helpers ────────────────────────────────────────────────────

    /**
     * Returns a short human-readable booking reference, e.g. "BK-000042".
     */
    public function referenceNumber(): string
    {
        return 'BK-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Number of nights between check-in and check-out.
     */
    public function nightCount(): int
    {
        if (! $this->check_in_date || ! $this->check_out_date) {
            return 0;
        }

        return (int) Carbon::parse($this->check_in_date)
            ->diffInDays(Carbon::parse($this->check_out_date));
    }

    /**
     * Returns a Tailwind CSS badge colour class for the current status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->booking_status) {
            self::STATUS_PENDING     => 'bg-yellow-100 text-yellow-800',
            self::STATUS_BOOKED      => 'bg-blue-100 text-blue-800',
            self::STATUS_CHECKED_IN  => 'bg-green-100 text-green-800',
            self::STATUS_CHECKED_OUT => 'bg-gray-100 text-gray-800',
            self::STATUS_CANCELLED   => 'bg-red-100 text-red-800',
            self::STATUS_NO_SHOW     => 'bg-orange-100 text-orange-800',
            self::STATUS_RELOCATED   => 'bg-purple-100 text-purple-800',
            self::STATUS_SNATCHED    => 'bg-pink-100 text-pink-800',
            default                  => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Human-readable status label.
     */
    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->booking_status] ?? ucfirst($this->booking_status);
    }
}
