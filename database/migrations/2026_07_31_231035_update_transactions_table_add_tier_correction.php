<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add tier_correction to payment_for
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_for ENUM('booking', 'stay_extension', 'modification_charge', 'modification_refund', 'tier_correction') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_for ENUM('booking', 'stay_extension', 'modification_charge', 'modification_refund') NOT NULL");
    }
};
