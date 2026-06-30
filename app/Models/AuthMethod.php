<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuthMethod Model
 *
 * Represents an OAuth / social login provider linked to a guest account.
 * A guest can have multiple providers (e.g. Google and Facebook) linked
 * to the same guest profile.
 *
 * @property int    $id
 * @property int    $guest_id
 * @property string $provider      e.g. 'google', 'facebook', 'github'
 * @property string $provider_key  The OAuth provider's unique user ID
 */
class AuthMethod extends Model
{
    protected $fillable = [
        'guest_id',
        'provider',
        'provider_key',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The guest profile this OAuth account belongs to.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
