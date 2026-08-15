<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Booking Room Pivot Table
 *
 * Supports the new Multi-Room Booking (Mix & Match) feature. Instead of a
 * single room_id on the bookings table, a booking can now reference multiple
 * room types at different quantities in a single checkout.
 *
 * Columns:
 *   booking_id      → FK to bookings
 *   room_type_id    → FK to room_types (the type the guest chose)
 *   room_id         → nullable FK to rooms (the physical room assigned at check-in)
 *   quantity        → number of rooms of this type in the booking
 *   price_at_booking→ the nightly price per room at time of booking (locked in)
 *
 * The existing bookings.room_id column is preserved for backward
 * compatibility with single-room bookings and existing reception flows.
 * Over time, all assignment logic will migrate to this table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_room', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->foreignId('room_type_id')
                ->constrained('room_types')
                ->restrictOnDelete();

            // The specific physical room assigned at check-in.
            // Null until the receptionist assigns a room.
            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->nullOnDelete();

            // Number of rooms of this type in the booking (min 1).
            $table->unsignedSmallInteger('quantity')->default(1);

            // Price per night per room, locked in at time of booking.
            // Protects the guest against future price changes.
            $table->decimal('price_at_booking', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_room');
    }
};
