<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rooms Table
 *
 * Stores all hotel rooms with their type, pricing, and availability status.
 * Replaces the old `rooms` table (with legacy columns) and the separate
 * `room_types` table — room type is now an enum directly on this table
 * for simplicity, as the DFD shows no separate type management workflow.
 *
 * Pricing is stored directly per room (price_per_night) rather than via
 * a separate room_types table, allowing individual room price overrides.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            // Short identifier displayed to guests and staff, e.g. '101', '2B'.
            $table->string('room_number', 5)->unique()->nullable();

            // Tracks whether the room is free to book or currently occupied.
            // Empty strings removed from SQL dump — phpMyAdmin artifact.
            $table->enum('current_status', ['available', 'occupied'])
                ->default('available');

            // Room category determines amenities and pricing tier.
            $table->enum('room_type', [
                'standard_twin',
                'standard_double',
                'deluxe_double',
                'family_room',
                'suite',
            ])->nullable();

            // Nightly rate for this specific room. Nullable to allow rooms
            // to be created before pricing is set.
            $table->decimal('price_per_night', 10, 2)->nullable();

            // Maximum number of guests the room can accommodate.
            $table->unsignedInteger('capacity')->nullable();

            // Optional marketing description shown on room listing and detail pages.
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
