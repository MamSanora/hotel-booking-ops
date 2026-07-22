<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // 1. Add 'partial' to the ENUM
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('pending', 'half', 'partial', 'full', 'refunded') NOT NULL DEFAULT 'pending'");
        }
        
        // 2. Update existing 'half' records to 'partial'
        DB::table('transactions')->where('payment_status', 'half')->update(['payment_status' => 'partial']);
        
        if (DB::getDriverName() !== 'sqlite') {
            // 3. Remove 'half' from the ENUM
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('pending', 'partial', 'full', 'refunded') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // 1. Add 'half' to the ENUM
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('pending', 'half', 'partial', 'full', 'refunded') NOT NULL DEFAULT 'pending'");
        }
        
        // 2. Revert existing 'partial' records to 'half'
        DB::table('transactions')->where('payment_status', 'partial')->update(['payment_status' => 'half']);
        
        if (DB::getDriverName() !== 'sqlite') {
            // 3. Remove 'partial' from the ENUM
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('pending', 'half', 'full', 'refunded') NOT NULL DEFAULT 'pending'");
        }
    }
};
