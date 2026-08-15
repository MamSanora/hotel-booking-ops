<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BookingRoom Model
 *
 * Pivot model for the booking_room table. Each row represents one
 * room type (and its quantity) that is part of a booking.
 *
 * This supports the Multi-Room Booking feature, where a single
 * booking can contain e.g. 2 Standard rooms and 1 Deluxe room.
 *
 * @property int   $booking_id
 * @property int   $room_type_id
 * @property int|null $room_id        Physical room assigned at check-in
 * @property int   $quantity
 * @property float $price_at_booking  Nightly price per room, locked at booking time
 */
class BookingRoom extends Model
{
    protected $table = 'booking_room';

    protected $fillable = [
        'booking_id',
        'room_type_id',
        'room_id',
        'quantity',
        'price_at_booking',
    ];

    protected function casts(): array
    {
        return [
            'quantity'         => 'integer',
            'price_at_booking' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Total cost for this line (price_at_booking × quantity × nights).
     * Nights are pulled from the parent booking.
     */
    public function lineTotal(): float
    {
        $nights = $this->booking?->nightCount() ?? 1;
        return round((float) $this->price_at_booking * $this->quantity * $nights, 2);
    }
}
