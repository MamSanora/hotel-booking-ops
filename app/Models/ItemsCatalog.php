<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ItemsCatalog Model
 *
 * Represents a requestable item available to guests during their stay.
 * Items are categorised (amenity, bedding, beverage) and managed by admins.
 * Guests select from this catalog when submitting a room service request.
 *
 * @property int         $id
 * @property string      $item_name
 * @property string|null $category          'amenity' | 'bedding' | 'beverage'
 * @property int|null    $created_by_admin_id
 */
class ItemsCatalog extends Model
{
    protected $table = 'items_catalogs';

    protected $fillable = [
        'item_name',
        'category',
        'created_by_admin_id',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The admin who added this item to the catalog.
     * Nullable — the item persists if the admin is deleted.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * All room service request line items referencing this catalog entry.
     */
    public function requestedItems(): HasMany
    {
        return $this->hasMany(RequestedItem::class, 'catalog_id');
    }

    // ── Display Helpers ────────────────────────────────────────────────────

    public function displayCategory(): string
    {
        return match ($this->category) {
            'amenity'  => 'Amenity',
            'bedding'  => 'Bedding',
            'beverage' => 'Beverage',
            default    => '—',
        };
    }
}
