<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'khqr', 'aba_payway', 'khqr_aba') NULL");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                DB::statement("UPDATE transactions SET payment_method = 'khqr' WHERE payment_method IN ('aba_payway', 'khqr_aba')");
            }
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash', 'khqr') NULL");
            }
        }
    }
};
