<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Incidental Items Table
 *
 * A catalog of predefined, chargeable damage types that a cleaner or
 * receptionist can select when logging an incidental charge.
 *
 * This table acts as a dropdown template — selecting an item pre-fills
 * the description and default amount in the incidental_charges form,
 * while still allowing the receptionist to override the amount.
 *
 * Examples: "Broken Flat-screen TV" ($300), "Lost Room Key" ($10),
 *           "Smoking in Room Penalty" ($50).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidental_items', function (Blueprint $table) {
            $table->id();

            // Human-readable label shown to staff in the dropdown.
            // e.g. "Broken Drinking Glass", "Broken/Lost TV Remote"
            $table->string('name', 100);

            // Default replacement/penalty cost in USD.
            // Receptionist can override this at the time of charging.
            $table->decimal('default_amount', 10, 2)->default(0.00);

            // Optional internal note for staff guidance.
            // e.g. "Only charge if physically broken, not just dead battery"
            $table->text('charge_policy')->nullable();

            // Soft toggle to hide discontinued items without deleting history.
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidental_items');
    }
};
