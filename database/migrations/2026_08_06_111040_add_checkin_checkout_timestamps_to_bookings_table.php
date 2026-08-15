<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Actual Check-in / Check-out Timestamps to Bookings
 *
 * The existing check_in_date and check_out_date columns are DATE-only
 * (no time component), so they always display as 00:00 when formatted.
 *
 * These two new DATETIME columns are stamped with now() when the
 * receptionist clicks Check-in / Check-out, giving the receipt an
 * accurate, immutable timestamp that is never overwritten by subsequent
 * booking edits (unlike updated_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('actual_check_in_at')
                ->nullable()
                ->after('check_out_date')
                ->comment('Exact moment the receptionist clicked Check-in.');

            $table->dateTime('actual_check_out_at')
                ->nullable()
                ->after('actual_check_in_at')
                ->comment('Exact moment the receptionist clicked Check-out.');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['actual_check_in_at', 'actual_check_out_at']);
        });
    }
};
