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
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['merchant_reference', 'payment_link', 'qr_code_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('merchant_reference')->nullable()->after('transaction_id');
            $table->text('payment_link')->nullable()->after('merchant_reference');
            $table->text('qr_code_url')->nullable()->after('payment_link');
        });
    }
};

