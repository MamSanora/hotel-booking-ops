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
        DB::table('rooms')->where('room_type', 'suite')->update(['room_type' => 'family_room']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
