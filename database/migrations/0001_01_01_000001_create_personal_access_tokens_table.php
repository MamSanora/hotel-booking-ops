<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal Access Tokens Table (Laravel Sanctum)
 *
 * Supports API token authentication for any authenticatable model via
 * Sanctum's polymorphic `tokenable` relationship. This allows GuestAuth,
 * Admin, and Staff models to issue and validate API tokens if needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();

            // Polymorphic: works with GuestAuth, Admin, or Staff models
            $table->morphs('tokenable');

            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
