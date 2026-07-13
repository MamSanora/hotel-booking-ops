<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'khqr', 'aba_payway') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE transactions SET payment_method = 'khqr' WHERE payment_method = 'aba_payway'");
            DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'khqr') NULL");
        }
    }
};
