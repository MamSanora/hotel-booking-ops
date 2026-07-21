<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            // Dynamic overbooking multiplier, tuned nightly by OptimizeOverbooking command.
            // Replaces the hardcoded OVERBOOKING_MULTIPLIER constant on RoomType.
            // Clamped at runtime between 1.00 (no overbooking) and 1.50 (aggressive).
            $table->decimal('overbooking_multiplier', 4, 2)->default(1.10)->after('capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('overbooking_multiplier');
        });
    }
};
