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
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['room_id', 'booking_status', 'check_in_date', 'check_out_date'], 'bookings_overlap_avail_idx');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['payment_status', 'amount_paid', 'payment_method', 'updated_at'], 'transactions_amount_collision_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_overlap_avail_idx');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_amount_collision_idx');
        });
    }
};
