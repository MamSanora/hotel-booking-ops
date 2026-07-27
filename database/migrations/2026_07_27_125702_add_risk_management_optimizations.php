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
        // 1. Add Soft Deletes (Audit & Risk Management)
        Schema::table('bookings', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('guests', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('room_services', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('requested_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        // 2. Add Search & Reporting Indexes (Performance)
        Schema::table('guests', function (Blueprint $table) {
            $table->index('full_name', 'guests_full_name_index');
        });
        Schema::table('phones', function (Blueprint $table) {
            $table->index('phone_number', 'phones_phone_number_index');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('created_at', 'transactions_created_at_index');
        });
        Schema::table('room_services', function (Blueprint $table) {
            $table->index('created_at', 'room_services_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove Search & Reporting Indexes
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex('guests_full_name_index');
        });
        Schema::table('phones', function (Blueprint $table) {
            $table->dropIndex('phones_phone_number_index');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_created_at_index');
        });
        Schema::table('room_services', function (Blueprint $table) {
            $table->dropIndex('room_services_created_at_index');
        });

        // Remove Soft Deletes
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('guests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('room_services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('requested_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
