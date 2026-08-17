<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop booking_room.quantity
 *
 * The quantity column was designed for a "count-per-row" approach (e.g. 2
 * Standard rooms in one row). The correct industry-standard approach is one row
 * per physical room — room count is derived by counting rows grouped by
 * room_type_id. Removing quantity enforces this one-row-per-room model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_room', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('booking_room', function (Blueprint $table) {
            $table->unsignedSmallInteger('quantity')->default(1)->after('room_id');
        });
    }
};
