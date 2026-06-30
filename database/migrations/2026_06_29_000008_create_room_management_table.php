<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Room Management Table
 *
 * An append-only audit log that records every significant administrative
 * action performed on a room. Each row is immutable once written — only
 * `created_at` is stored (no `updated_at`).
 *
 * Cascade delete on both foreign keys: if a room or admin is removed, the
 * associated log entries are also removed (matches the SQL schema).
 *
 * Action values:
 *   - add_room    : A new room was created by an admin.
 *   - update_price: The room's price_per_night was changed by an admin.
 *                   (Renamed from 'price_per_night' in the SQL dump for
 *                   clarity — 'price_per_night' reads as a field name,
 *                   not an action verb.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_management', function (Blueprint $table) {
            $table->id();

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();

            $table->foreignId('managed_by_admin_id')
                ->constrained('admins')
                ->cascadeOnDelete();

            // Empty string removed from SQL dump — phpMyAdmin artifact.
            // 'price_per_night' renamed to 'update_price' for verb clarity.
            $table->enum('action', ['add_room', 'update_price']);

            // Audit log rows are created once and never modified.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_management');
    }
};
