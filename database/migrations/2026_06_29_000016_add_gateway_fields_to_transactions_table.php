<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add ABA PayWay gateway-specific columns to the transactions table.
 *
 * These nullable fields are only populated for KHQR (ABA PayWay) transactions.
 * Cash transactions leave them null. This avoids a separate payment_attempts
 * table for what is a small amount of extra metadata per KHQR payment.
 *
 * transaction_id      — Unique reference sent to and returned by ABA PayWay.
 * merchant_reference  — Human-readable booking reference used in the payload.
 * payment_link        — Full ABA PayWay deep-link URL.
 * qr_code_url         — URL of the generated QR code image.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->unique()->after('booking_id');
            $table->string('merchant_reference')->nullable()->after('transaction_id');
            $table->text('payment_link')->nullable()->after('merchant_reference');
            $table->text('qr_code_url')->nullable()->after('payment_link');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'merchant_reference', 'payment_link', 'qr_code_url']);
        });
    }
};
