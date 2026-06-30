<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RequestedItem Model
 *
 * Junction model linking a room service request to specific catalog items.
 * Records which items were requested and in what quantity.
 *
 * Rows are append-only — no updated_at timestamp is stored.
 *
 * @property int $id
 * @property int $request_id
 * @property int $catalog_id
 * @property int $amount_per_item
 */
class RequestedItem extends Model
{
    // This model is append-only; no updated_at column exists on the table.
    public const UPDATED_AT = null;

    protected $table = 'requested_items';

    protected $fillable = [
        'request_id',
        'catalog_id',
        'amount_per_item',
    ];

    protected function casts(): array
    {
        return [
            'amount_per_item' => 'integer',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The room service request this item line belongs to.
     */
    public function roomService(): BelongsTo
    {
        return $this->belongsTo(RoomService::class, 'request_id');
    }

    /**
     * The catalog item being requested.
     */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(ItemsCatalog::class, 'catalog_id');
    }
}
