<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guest Auths Table
 *
 * Stores login credentials for guests who register an online account.
 * This table is intentionally separate from `guests` (profile data) so that
 * walk-in and phone-booking guests can exist without login credentials.
 *
 * The `GuestAuth` model extends Authenticatable and is used by the 'web'
 * guard. Email is the login identifier; passwordhash stores the bcrypt hash.
 *
 * Cascade delete: removing a guest also removes their auth credentials.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_auths', function (Blueprint $table) {
            $table->id();

            // One-to-one with guests. Cascade ensures no orphaned auth rows.
            $table->foreignId('guest_id')
                ->unique()
                ->constrained('guests')
                ->cascadeOnDelete();

            // Used as the login identifier and for password reset emails.
            $table->string('email')->unique();

            // Named `passwordhash` to match the SQL schema. GuestAuth model
            // overrides getAuthPassword() to return this column name.
            $table->string('passwordhash');

            // Tracks whether the guest has verified their email address.
            // Not in the original SQL dump but required for the email
            // verification flow (verify-email.blade.php already exists).
            $table->timestamp('email_verified_at')->nullable();

            // Required by Laravel's Authenticatable contract for "remember me".
            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_auths');
    }
};
