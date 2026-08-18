<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * IncidentalCharge Model
 *
 * Records an ad-hoc charge applied by a receptionist at check-out time.
 * Examples: broken lamp, minibar consumption, late check-out fee.
 *
 * Each charge is linked to a booking and optionally to the transaction
 * that settled it. The receipt loops through all incidental charges for
 * a booking to display them as separate line items.
 *
 * @property int       $booking_id
 * @property int|null  $room_id              The specific room the damage occurred in (multi-room support)
 * @property int|null  $transaction_id
 * @property int|null  $reported_by_staff_id Staff member (cleaner/receptionist) who filed the charge
 * @property string    $description
 * @property int       $quantity
 * @property float     $amount           Price per unit
 * @property float     $total_amount     quantity × amount (stored for receipt convenience)
 */
class IncidentalCharge extends Model
{
    use SoftDeletes;

    protected $table = 'incidental_charges';

    protected $fillable = [
        'booking_id',
        'room_id',
        'transaction_id',
        'reported_by_staff_id',
        'description',
        'quantity',
        'amount',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity'     => 'integer',
            'amount'       => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'reported_by_staff_id');
    }
}
