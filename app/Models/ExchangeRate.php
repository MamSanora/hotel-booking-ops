<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ExchangeRate
 *
 * Represents a single historical exchange rate snapshot.
 * The latest row (by fetched_at) is always the active rate used by the application.
 *
 * @property int    $id
 * @property string $base        e.g. "USD"
 * @property string $target      e.g. "KHR"
 * @property float  $rate        e.g. 4100.0000
 * @property string $source      e.g. "frankfurter_nbc" | "fallback"
 * @property \Carbon\Carbon $fetched_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExchangeRate extends Model
{
    protected $fillable = [
        'base',
        'target',
        'rate',
        'source',
        'fetched_at',
    ];

    protected $casts = [
        'rate'       => 'float',
        'fetched_at' => 'datetime',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────

    /**
     * Scope: only USD→KHR rates, ordered newest first.
     */
    public function scopeUsdToKhr($query)
    {
        return $query->where('base', 'USD')
                     ->where('target', 'KHR')
                     ->orderByDesc('fetched_at');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Is this rate considered stale (older than 24 hours)?
     */
    public function isStale(): bool
    {
        return $this->fetched_at->diffInHours(now()) > 24;
    }

    /**
     * Human-readable status label for use in Blade templates.
     */
    public function statusLabel(): string
    {
        return $this->isStale() ? 'Stale' : 'Live';
    }
}
