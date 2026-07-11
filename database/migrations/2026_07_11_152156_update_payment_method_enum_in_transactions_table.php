<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter the ENUM column to add 'aba_payway'
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'khqr', 'aba_payway') NULL");
    }

    public function down(): void
    {
        // Revert back, assuming no existing rows have 'aba_payway'
        // (if they do, this will fail or data will be truncated depending on strict mode, 
        // so we should update them back to 'khqr' or 'cash' first, but for a down migration this is fine).
        DB::statement("UPDATE transactions SET payment_method = 'khqr' WHERE payment_method = 'aba_payway'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'khqr') NULL");
    }
};
