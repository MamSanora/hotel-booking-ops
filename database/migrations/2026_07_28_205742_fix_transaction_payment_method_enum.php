<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'aba_telegram' to the transactions.payment_method enum.
     * Also add 'failed' and 'refund_pending' to payment_status enum to match PHP constants.
     */
    public function up(): void
    {
        // Expand payment_method enum to include aba_telegram
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transactions MODIFY payment_method ENUM('cash','khqr','aba_payway','khqr_aba','aba_telegram') NULL");
        }

        // Expand payment_status enum to include 'failed' and 'refund_pending' (match PHP constants)
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transactions MODIFY payment_status ENUM('pending','partial','full','refunded','failed','refund_pending') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transactions MODIFY payment_method ENUM('cash','khqr','aba_payway','khqr_aba') NULL");
        }
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transactions MODIFY payment_status ENUM('pending','partial','full','refunded') NOT NULL DEFAULT 'pending'");
        }
    }
};
