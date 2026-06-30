<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transactions Table
 *
 * Records all payment transactions for bookings. Replaces the old complex
 * `payments` table (which had Stripe/ABA PayWay-specific columns) with a
 * simpler, method-agnostic design.
 *
 * A booking can have multiple transaction rows — e.g. one for the initial
 * booking payment and one for a stay extension. The `payment_status` of
 * 'half' supports partial payment scenarios (Process 3.2 "Confirm Remaining
 * Balance" in the DFD).
 *
 * Payment methods:
 *   cash  → Handled directly at the front desk by a receptionist.
 *   khqr  → Cambodian QR payment standard (processed via ABA PayWay API).
 *
 * Payment status:
 *   pending   → Payment initiated but not yet confirmed.
 *   half      → Partial payment received; remaining balance outstanding.
 *   full      → Payment completed in full.
 *   refunded  → Payment was reversed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // The booking this payment applies to. Cascade delete ensures
            // transaction records are cleaned up with the booking.
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            $table->decimal('amount_paid', 10, 2)->default(0.00);

            // What this payment covers — the initial booking or a stay extension.
            // Empty strings removed from SQL dump — phpMyAdmin artifact.
            $table->enum('payment_for', ['booking', 'stay_extension'])->nullable();

            // How the guest paid. KHQR is processed via the ABA PayWay API.
            // Empty strings removed from SQL dump — phpMyAdmin artifact.
            $table->enum('payment_method', ['cash', 'khqr'])->nullable();

            $table->enum('payment_status', ['pending', 'half', 'full', 'refunded'])
                ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
