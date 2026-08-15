<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add extension_nights and extension_new_checkout to transactions.
     * These store the intended extension so the booking dates are only
     * updated AFTER payment is confirmed by the webhook, not before.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedSmallInteger('extension_nights')->nullable()->after('payment_for')
                ->comment('Number of extra nights for stay_extension transactions');
            $table->date('extension_new_checkout')->nullable()->after('extension_nights')
                ->comment('Proposed new checkout date, applied after payment confirmation');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['extension_nights', 'extension_new_checkout']);
        });
    }
};
