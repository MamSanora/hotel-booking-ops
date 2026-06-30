<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guests Table
 *
 * Stores guest profile information only — no authentication credentials.
 * This intentional separation allows guests to exist in the system without
 * a login account, covering walk-in guests, phone bookings, and proxy
 * bookings made by receptionists.
 *
 * Registered guests (online self-booking) additionally have a record in
 * `guest_auths` (credentials) and optionally `auth_methods` (OAuth).
 *
 * Replaces the old `users` and `customers` tables from the previous schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();

            $table->string('full_name', 50);

            // Gender is optional — guests may decline to specify.
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])
                ->nullable();

            // Country/nationality for reporting and ID verification purposes.
            $table->string('nationality', 50)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
