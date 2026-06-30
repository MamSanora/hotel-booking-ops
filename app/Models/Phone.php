<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phone Model
 *
 * Stores one or more phone numbers per guest. Separated into its own
 * table to support guests with multiple contact numbers.
 *
 * @property int    $id
 * @property int    $guest_id
 * @property string $phone_number
 */
class Phone extends Model
{
    protected $fillable = [
        'guest_id',
        'phone_number',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The guest this phone number belongs to.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
