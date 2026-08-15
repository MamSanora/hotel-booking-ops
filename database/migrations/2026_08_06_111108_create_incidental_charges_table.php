<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Incidental Charges Table
 *
 * Tracks ad-hoc charges applied by a receptionist at check-out time.
 * Examples: broken lamp ($15), minibar consumption ($12), late check-out fee ($20).
 *
 * Workflow:
 *   1. Receptionist clicks "Check-out" on a booking.
 *   2. A modal intercepts the action and prompts for optional charges.
 *   3. Each charge is saved here, increasing the booking's total_price.
 *   4. The receptionist must settle the new balance before the booking
 *      is marked as checked-out.
 *   5. The receipt loops through this table to display each charge as a
 *      separate line item.
 *
 * Columns:
 *   booking_id     → which booking this charge belongs to
 *   transaction_id → nullable, links to the payment transaction that settled it
 *   description    → free-text label (e.g. "Broken lamp replacement")
 *   quantity       → unit count (e.g. 2 drinks)
 *   amount         → price per unit
 *   total_amount   → computed: quantity × amount (stored for receipt convenience)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidental_charges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();

            // Links to the transaction that settled this charge.
            // Null until payment is collected.
            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->string('description');

            $table->unsignedSmallInteger('quantity')->default(1);

            // Price per unit in USD.
            $table->decimal('amount', 10, 2);

            // Stored denormalized for receipt display (quantity × amount).
            $table->decimal('total_amount', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidental_charges');
    }
};
