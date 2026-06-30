<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auth Methods Table
 *
 * Stores OAuth / social login provider credentials for guests.
 * A single guest can link multiple providers (e.g. Google AND Facebook).
 *
 * The composite unique key on (provider, provider_key) guarantees that the
 * same social account can only be linked to one guest at a time.
 *
 * Cascade delete: removing a guest also removes all their linked OAuth accounts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_methods', function (Blueprint $table) {
            $table->id();

            // The guest this OAuth account belongs to.
            $table->foreignId('guest_id')
                ->constrained('guests')
                ->cascadeOnDelete();

            // OAuth provider name, e.g. 'google', 'facebook', 'github'.
            $table->string('provider', 50);

            // The unique user identifier returned by the OAuth provider.
            $table->string('provider_key');

            // Prevents the same OAuth account from being claimed by two guests.
            $table->unique(['provider', 'provider_key']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_methods');
    }
};
