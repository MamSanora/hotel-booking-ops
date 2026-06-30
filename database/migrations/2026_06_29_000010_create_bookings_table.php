<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bookings Table
 *
 * Core operational table tracking every room reservation from creation
 * through check-out. Supports both self-service (online) and proxy bookings
 * (walk-in, phone) made by receptionists on behalf of guests.
 *
 * Status lifecycle:
 *   pending     → Guest initiated booking, payment not yet confirmed.
 *   booked      → Payment confirmed; room is reserved.
 *   checked-in  → Guest has arrived and checked in at the front desk.
 *   checked-out → Guest has departed; room returned to available.
 *   cancelled   → Booking was cancelled before check-in.
 *   no_show     → Guest did not arrive on the check-in date.
 *
 * All three foreign keys use SET NULL on delete so that historical booking
 * records are preserved even if the linked guest, room, or staff member
 * is removed from the system.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // The guest this booking belongs to. Nullable (SET NULL) so
            // booking history survives guest account deletion.
            $table->foreignId('guest_id')
                ->nullable()
                ->constrained('guests')
                ->nullOnDelete();

            // The specific room assigned to this booking.
            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->nullOnDelete();

            // The staff member who processed this booking (for proxy bookings).
            // NULL for self-service bookings made directly by the guest online.
            $table->foreignId('handled_by_staff_id')
                ->nullable()
                ->constrained('staff')
                ->nullOnDelete();

            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();

            // Tracks how many times the guest has extended their stay
            // (Process 5.0 Stay Extension in the DFD).
            $table->unsignedInteger('number_of_stay_extension')->default(0);

            // Total booking cost including any stay extensions.
            $table->decimal('total_price', 10, 2)->nullable();

            // Booking lifecycle status. Default is 'booked' for walk-in
            // bookings where payment is handled immediately. Online self-
            // bookings start as 'pending' until payment is confirmed.
            $table->enum('booking_status', [
                'pending',
                'booked',
                'checked-in',
                'checked-out',
                'cancelled',
                'no_show',
            ])->default('booked');

            // Records how the booking was initiated.
            $table->enum('guest_type', ['user', 'walk-in', 'phone', 'other'])
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
