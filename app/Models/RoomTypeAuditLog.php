<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RoomManagement Model
 *
 * Represents a single audit log entry recording an administrative action
 * performed on a room. Rows are append-only — never updated after creation.
 *
 * Actions:
 *   add_room     → Admin added a new room to the system.
 *   update_price → Admin changed the room's price_per_night.
 *
 * @property int    $id
 * @property int    $room_type_id
 * @property int    $managed_by_admin_id
 * @property string $action              'add_room_type' | 'update_price'
 * @property string $created_at
 */
class RoomTypeAuditLog extends Model
{
    // This model has no updated_at column — it is an append-only audit log.
    public const UPDATED_AT = null;

    protected $table = 'room_type_audit_logs';

    protected $fillable = [
        'room_type_id',
        'managed_by_admin_id',
        'action',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /**
     * The room type this audit entry is about.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * The admin who performed the action.
     */
    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'managed_by_admin_id');
    }

    // ── Display Helpers ────────────────────────────────────────────────────

    public function displayAction(): string
    {
        return match ($this->action) {
            'add_room_type' => 'Added Room Type',
            'update_price'  => 'Updated Price',
            default         => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
