<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Room Types Table
 *
 * Resolves the 3NF violation in the `rooms` table by extracting the
 * type-level attributes (capacity, price_per_night, description) into
 * their own lookup table.
 *
 * Before this migration, every physical room row duplicated these values.
 * For example, all 21 Standard Twin rooms stored the same $35 price and
 * the same description string — an Update Anomaly waiting to happen.
 *
 * After this migration, a single row in `room_types` defines each category,
 * and `rooms` references it via `room_type_id`. Changing a price or
 * description now requires updating exactly one row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();

            // Machine-readable slug, matches the previous enum values in `rooms`.
            // e.g. 'standard_twin', 'standard_double', 'deluxe_double'
            $table->string('slug', 50)->unique();

            // Human-readable display name shown to guests and in admin UI.
            $table->string('display_name', 100);

            // Maximum occupancy for this room category.
            $table->unsignedInteger('capacity');

            // Base nightly rate for all rooms of this type.
            $table->decimal('price_per_night', 10, 2);

            // Marketing description shown on the rooms listing and detail pages.
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
