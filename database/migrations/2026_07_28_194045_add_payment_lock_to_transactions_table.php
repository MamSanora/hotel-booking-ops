<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // When a guest opens the payment page, we stamp both columns.
            // payment_locked_at   = wall-clock time the lock was acquired
            // payment_lock_expires_at = payment_locked_at + 15 minutes
            // Both are cleared when the payment is confirmed or abandoned.
            $table->timestamp('payment_locked_at')->nullable()->after('tracking_status');
            $table->timestamp('payment_lock_expires_at')->nullable()->after('payment_locked_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_locked_at', 'payment_lock_expires_at']);
        });
    }
};
