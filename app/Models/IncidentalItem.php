<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * IncidentalItem Model
 *
 * A catalog of predefined damage types and penalties that staff can
 * select when filing an incidental charge against a guest's booking.
 *
 * Selecting an item pre-fills the description and default_amount in
 * the incidental charge form, while allowing the receptionist to
 * override the amount based on actual damage severity.
 *
 * @property int    $id
 * @property string $name            e.g. "Broken Flat-screen TV"
 * @property float  $default_amount  e.g. 300.00
 * @property string|null $charge_policy Internal staff guidance note
 * @property bool   $is_active       Soft-disable without losing history
 */
class IncidentalItem extends Model
{
    protected $fillable = [
        'name',
        'default_amount',
        'charge_policy',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'is_active'      => 'boolean',
        ];
    }

    /**
     * Scope: only return active items for dropdowns.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
