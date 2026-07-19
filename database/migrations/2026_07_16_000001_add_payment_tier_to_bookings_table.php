<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add payment_tier to bookings table + expand booking_status with 'snatched'.
 *
 * payment_tier stores the percentage of total_price the guest commits to paying
 * upfront as a deposit: 20, 50, or 100.
 *
 * Tier priority rules:
 *   100 > 50 > 20
 *
 *   A guest may book a room already held by a lower-tier booking, but is blocked
 *   from booking a room at the same or higher tier. This intentional "double-booking"
 *   is resolved by reception staff at check-in via the existing relocation flow.
 *
 * The 'snatched' booking_status identifies a booking that lost a same-tier race
 * condition (two guests paid the same tier concurrently; the slower one is snatched).
 * It is distinct from 'cancelled' which is guest-initiated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Payment tier: the percentage of total_price the guest pays upfront.
            // Allowed values: 20, 50, 100 (default full payment).
            $table->unsignedTinyInteger('payment_tier')
                ->default(100)
                ->after('total_price')
                ->comment('Upfront deposit tier: 20, 50, or 100 percent of total_price');
        });

        // Expand the booking_status enum to include 'snatched'.
        // Blueprint::enum() cannot alter an existing enum; raw SQL is required.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_status
                ENUM('pending','booked','checked-in','checked-out','cancelled','no_show','relocated','snatched')
                NOT NULL DEFAULT 'booked'");
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_tier');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_status
                ENUM('pending','booked','checked-in','checked-out','cancelled','no_show','relocated')
                NOT NULL DEFAULT 'booked'");
        }
    }
};
