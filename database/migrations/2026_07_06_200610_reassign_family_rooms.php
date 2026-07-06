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
        DB::table('rooms')->whereIn('room_number', ['409', '410', '411'])->update(['room_type' => 'standard_twin']);
        DB::table('rooms')->whereIn('room_number', ['412', '413'])->update(['room_type' => 'standard_double']);
        DB::table('rooms')->whereIn('room_number', ['414', '415'])->update(['room_type' => 'deluxe_double']);
        
        // Catch-all just in case
        DB::table('rooms')->where('room_type', 'family_room')->update(['room_type' => 'standard_twin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
