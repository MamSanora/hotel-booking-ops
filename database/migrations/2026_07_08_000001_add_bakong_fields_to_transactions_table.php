<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Bakong Open API / KHQR fields to the transactions table.
 *
 * These nullable fields are only populated for KHQR transactions that use
 * the Bakong payment switch (solo/individual merchant — Tag 29 KHQR).
 *
 * khqr_string      — The full EMVCo TLV string rendered as the QR code.
 *                    This is what the guest's banking app reads.
 * md5_hash         — MD5 hash of the khqr_string.
 *                    Used to query the Bakong Open API endpoint
 *                    POST /v1/check_transaction_by_md5 for payment verification.
 * tracking_status  — Last known status returned by the Bakong Open API
 *                    (e.g. 'RECEIVE_AT_RECEIVER_BANK').
 *
 * Note: The old ABA PayWay columns (transaction_id, merchant_reference,
 * payment_link, qr_code_url) from migration _0016 are retained untouched
 * for backwards compatibility and potential future use.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // The raw Tag-Length-Value KHQR string shown as the QR code.
            $table->text('khqr_string')->nullable()->after('qr_code_url');

            // MD5 hash of khqr_string — the primary key for Bakong API lookup.
            $table->string('md5_hash', 32)->nullable()->unique()->after('khqr_string');

            // Latest Bakong tracking status polled from the Open API.
            $table->string('tracking_status')->nullable()->after('md5_hash');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['khqr_string', 'md5_hash', 'tracking_status']);
        });
    }
};
