<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Indexes added to booking_room pivot table:
     *   - booking_id          → fast lookup of all rooms for a booking
     *   - room_type_id        → join performance when eager-loading roomType
     *   - room_id             → join performance when eager-loading room
     *   - (booking_id, room_type_id) composite → covers the most frequent
     *     query pattern: "all line-items for booking X of type Y"
     */
    public function up(): void
    {
        Schema::table('booking_room', function (Blueprint $table) {
            // Get existing index names to guard against duplicates.
            $existing = collect(Schema::getIndexes('booking_room'))->pluck('name')->all();

            if (!in_array('booking_room_booking_id_index', $existing)) {
                $table->index('booking_id', 'booking_room_booking_id_index');
            }
            if (!in_array('booking_room_room_type_id_index', $existing)) {
                $table->index('room_type_id', 'booking_room_room_type_id_index');
            }
            if (!in_array('booking_room_room_id_index', $existing)) {
                $table->index('room_id', 'booking_room_room_id_index');
            }
            if (!in_array('booking_room_booking_id_room_type_id_index', $existing)) {
                $table->index(['booking_id', 'room_type_id'], 'booking_room_booking_id_room_type_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_room', function (Blueprint $table) {
            $table->dropIndex('booking_room_booking_id_index');
            $table->dropIndex('booking_room_room_type_id_index');
            $table->dropIndex('booking_room_room_id_index');
            $table->dropIndex('booking_room_booking_id_room_type_id_index');
        });
    }
};
