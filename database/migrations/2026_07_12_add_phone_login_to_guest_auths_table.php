<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Phone Login Support to guest_auths
 *
 * Changes:
 *  1. Make `email` nullable — guests registering with phone only won't have one.
 *  2. Add `login_phone`     — the phone number used as a login identifier (unique).
 *  3. Add `phone_verified_at` — tracks whether the phone OTP was confirmed.
 *  4. Add `otp_code`          — stores the temporary 6-digit code (mock SMS).
 *  5. Add `otp_expires_at`    — when the OTP becomes invalid (10 minutes).
 *
 * Safety notes:
 *  - All 8 existing rows have emails, so making email nullable does not corrupt data.
 *  - MySQL allows multiple NULLs in a UNIQUE column — no collisions for existing rows.
 *  - `login_phone` is separate from the `phones` contact table: different semantic role.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_auths', function (Blueprint $table) {
            // Make email optional (phone-only users won't supply one).
            $table->string('email')->nullable()->change();

            // Login identifier for phone-based accounts.
            $table->string('login_phone', 30)->nullable()->unique()->after('email');

            // OTP verification tracking.
            $table->timestamp('phone_verified_at')->nullable()->after('login_phone');
            $table->string('otp_code', 6)->nullable()->after('phone_verified_at');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
        });
    }

    public function down(): void
    {
        Schema::table('guest_auths', function (Blueprint $table) {
            $table->dropColumn(['login_phone', 'phone_verified_at', 'otp_code', 'otp_expires_at']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
