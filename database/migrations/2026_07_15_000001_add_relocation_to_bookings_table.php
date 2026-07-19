<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds room relocation support to the bookings table.
 *
 * Changes:
 *   1. Adds a self-referential nullable FK: relocated_to_booking_id
 *      Points to the new booking created when a guest is moved to another room.
 *      This preserves the chain: Booking A (relocated) → Booking B (checked-in).
 *
 *   2. Adds 'relocated' to the booking_status enum.
 *      'relocated' means the guest left this room to continue their stay
 *      in a different room. It is NOT a checkout — the guest is still in-house.
 *
 *   3. Adds 'special_requests' column (nullable text) for guest notes.
 *      Included here if not already present from a prior migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Self-referential FK: the booking the guest was relocated to.
            // Nullable — only set for bookings with status 'relocated'.
            // SET NULL on delete so analytics rows survive if the new booking
            // is later deleted (e.g. test cleanup).
            $table->foreignId('relocated_to_booking_id')
                ->nullable()
                ->after('guest_type')
                ->constrained('bookings')
                ->nullOnDelete();
        });

        // Modify the ENUM to include 'relocated'.
        // Laravel Blueprint doesn't support ENUM modification natively; use raw SQL.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_status ENUM(
                'pending',
                'booked',
                'checked-in',
                'checked-out',
                'cancelled',
                'no_show',
                'relocated'
            ) NOT NULL DEFAULT 'booked'");
        }

        // Add special_requests if not present
        if (!Schema::hasColumn('bookings', 'special_requests')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->text('special_requests')->nullable()->after('guest_type');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['relocated_to_booking_id']);
            $table->dropColumn('relocated_to_booking_id');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN booking_status ENUM(
                'pending',
                'booked',
                'checked-in',
                'checked-out',
                'cancelled',
                'no_show'
            ) NOT NULL DEFAULT 'booked'");
        }
    }
};
