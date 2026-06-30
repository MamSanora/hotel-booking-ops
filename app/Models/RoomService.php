<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RoomService Model
 *
 * Represents a guest request or complaint submitted during their stay.
 * Corresponds to Process 6.0 (Room Services) in the DFD.
 *
 * Guests submit requests from their room-accessible interface.
 * Receptionists view, claim, and respond to requests via their dashboard.
 *
 * @property int         $id
 * @property int         $booking_id
 * @property int|null    $handled_by_staff_id
 * @property string|null $request_type    'request' | 'complaint'
 * @property string|null $guest_notes
 * @property string      $request_status  'pending'|'confirmed'|'completed'|'cancelled'|'denied'
 * @property string|null $response
 */
class RoomService extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DENIED    = 'denied';

    public const TYPE_REQUEST   = 'request';
    public const TYPE_COMPLAINT = 'complaint';

    protected $table = 'room_services';

    protected $fillable = [
        'booking_id',
        'handled_by_staff_id',
        'request_type',
        'guest_notes',
        'request_status',
        'response',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The booking this request is associated with.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The staff member who handled or claimed this request.
     */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'handled_by_staff_id');
    }

    /**
     * The catalog items included in this request (for item-type requests).
     */
    public function requestedItems(): HasMany
    {
        return $this->hasMany(RequestedItem::class, 'request_id');
    }

    // ── Query Scopes ───────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('request_status', self::STATUS_PENDING);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('request_status', [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    // ── Status Helpers ─────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->request_status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->request_status === self::STATUS_COMPLETED;
    }

    public function isRequest(): bool
    {
        return $this->request_type === self::TYPE_REQUEST;
    }

    public function isComplaint(): bool
    {
        return $this->request_type === self::TYPE_COMPLAINT;
    }

    /**
     * Returns a Tailwind CSS badge colour class for the current status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->request_status) {
            self::STATUS_PENDING   => 'bg-yellow-100 text-yellow-800',
            self::STATUS_CONFIRMED => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-800',
            self::STATUS_DENIED    => 'bg-red-100 text-red-800',
            default                => 'bg-gray-100 text-gray-600',
        };
    }

    public function statusLabel(): string
    {
        return ucfirst($this->request_status);
    }
}
