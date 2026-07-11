<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();

            // Unique machine-readable identifier for each gateway.
            // Maps to the slug used in PaymentGatewayManager::$drivers.
            $table->string('slug')->unique();

            // Human-readable name shown in admin UI.
            $table->string('name');

            // Admin's manual control over visibility.
            // The PaymentGatewayManager may override 'active' → 'disabled'
            // when credentials are missing or the API is unreachable.
            $table->enum('admin_status', ['active', 'disabled', 'hidden'])
                  ->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
