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
        // Standard Twin
        DB::table('rooms')->where('room_type', 'standard_twin')->update([
            'capacity' => 2,
            'price_per_night' => 35.00,
            'description' => 'Comfortable 24–28 m² room featuring two single beds, air conditioning, private bathroom, and work desk. Perfect for friends or business travellers.',
        ]);

        // Standard Double
        DB::table('rooms')->where('room_type', 'standard_double')->update([
            'capacity' => 2,
            'price_per_night' => 50.00,
            'description' => 'Cosy 24–28 m² room with a queen-size bed, flat-screen TV, mini-fridge, and daily housekeeping. Great for couples visiting Phnom Penh.',
        ]);

        // Deluxe Double
        DB::table('rooms')->where('room_type', 'deluxe_double')->update([
            'capacity' => 2,
            'price_per_night' => 80.00,
            'description' => 'Spacious 32–36 m² room on the upper floors featuring a king-size bed, seating area, enhanced amenities, and city views.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration
    }
};
