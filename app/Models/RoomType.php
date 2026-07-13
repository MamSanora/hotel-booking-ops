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
}
