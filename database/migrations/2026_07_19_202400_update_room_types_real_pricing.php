<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('room_types')->where('slug', 'standard_twin')->update(['price_per_night' => 35.00]);
        DB::table('room_types')->where('slug', 'standard_double')->update(['price_per_night' => 50.00]);
        DB::table('room_types')->where('slug', 'deluxe_double')->update(['price_per_night' => 80.00]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed as this sets the official true rates
    }
};
