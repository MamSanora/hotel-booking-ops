<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop bookings.room_id
 *
 * The bookings.room_id column was a legacy single-room FK predating the
 * booking_room pivot table. Now that all room assignments live in booking_room
 * (one row per physical room), this column is redundant and misleading.
 * Removing it enforces booking_room as the single source of truth.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['room_id']);
            }
            $table->dropIndex('bookings_overlap_avail_idx');
            $table->dropIndex('idx_room_availability');
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropColumn('room_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->nullOnDelete()
                ->after('guest_id');
        });
    }
};
