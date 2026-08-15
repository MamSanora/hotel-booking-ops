<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'abandoned' to the booking_status ENUM column on the bookings table.
     * The full set of valid statuses must be listed when altering an ENUM in MySQL.
     */
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_status ENUM(
                'pending',
                'booked',
                'checked-in',
                'checked-out',
                'cancelled',
                'no_show',
                'relocated',
                'snatched',
                'abandoned'
            ) NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_status ENUM(
                'pending',
                'booked',
                'checked-in',
                'checked-out',
                'cancelled',
                'no_show',
                'relocated',
                'snatched'
            ) NOT NULL DEFAULT 'pending'");
        }
    }
};
