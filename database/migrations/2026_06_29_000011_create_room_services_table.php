<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Room Services Table
 *
 * Stores guest requests and complaints submitted during their stay
 * (Process 6.0 Room Services in the DFD). Each row represents one
 * request or complaint tied to an active booking.
 *
 * Receptionists view and respond to these via their dashboard. The
 * `handled_by_staff_id` is set when a staff member claims the request.
 *
 * Status lifecycle:
 *   pending   → Submitted by guest, awaiting staff acknowledgement.
 *   confirmed → Staff has acknowledged and is handling the request.
 *   completed → The request has been fulfilled.
 *   cancelled → Guest withdrew the request.
 *   denied    → Staff determined the request cannot be fulfilled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_services', function (Blueprint $table) {
            $table->id();

            // The booking this request is associated with. Cascade delete
            // ensures requests are removed when a booking is removed.
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            // The staff member handling this request. SET NULL if they leave.
            $table->foreignId('handled_by_staff_id')
                ->nullable()
                ->constrained('staff')
                ->nullOnDelete();

            // Distinguishes a guest request (item delivery, service) from
            // a complaint (issue to resolve). Empty strings removed from
            // SQL dump — phpMyAdmin artifact.
            $table->enum('request_type', ['request', 'complaint'])->nullable();

            // The guest's description of what they need or their complaint.
            $table->text('guest_notes')->nullable();

            $table->enum('request_status', [
                'pending',
                'confirmed',
                'completed',
                'cancelled',
                'denied',
            ])->default('pending');

            // Staff response or resolution notes visible to the guest.
            $table->text('response')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_services');
    }
};
