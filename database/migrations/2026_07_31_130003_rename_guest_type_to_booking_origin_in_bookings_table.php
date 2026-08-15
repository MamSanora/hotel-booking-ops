<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('bookings', function (Blueprint $table) {
                $table->renameColumn('guest_type', 'booking_origin');
            });
        } else {
            DB::statement("ALTER TABLE bookings CHANGE guest_type booking_origin ENUM('user', 'walk-in', 'phone', 'other', 'agoda') NULL DEFAULT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('bookings', function (Blueprint $table) {
                $table->renameColumn('booking_origin', 'guest_type');
            });
        } else {
            DB::statement("ALTER TABLE bookings CHANGE booking_origin guest_type ENUM('user', 'walk-in', 'phone', 'other') NULL DEFAULT NULL");
        }
    }
};

