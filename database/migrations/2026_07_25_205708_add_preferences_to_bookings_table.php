<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds guest preference columns to bookings.
 *
 * - bed_type:          Bed configuration requested ('twin' | 'double' | null).
 *                      Standard and Deluxe rooms offer both configurations.
 *                      Family Triple rooms default to a fixed setup (ignored field).
 *
 * - floor_preference:  Preferred floor number as a string ('2'...'5' | null).
 *                      Null = no preference. Reception uses this as a hint during
 *                      physical room assignment but it is not a guaranteed guarantee.
 *
 * - view_preference:   Preferred view ('balcony' | 'window' | null).
 *                      Null = no preference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('bed_type', 20)->nullable()->after('special_requests');
            $table->string('floor_preference', 10)->nullable()->after('bed_type');
            $table->string('view_preference', 20)->nullable()->after('floor_preference');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['bed_type', 'floor_preference', 'view_preference']);
        });
    }
};
