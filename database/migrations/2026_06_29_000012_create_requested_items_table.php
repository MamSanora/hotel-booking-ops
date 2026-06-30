<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Requested Items Table
 *
 * Junction table linking a room service request to specific catalog items.
 * A single request can include multiple items (e.g. 2 towels + 1 pillow).
 *
 * The composite unique key on (request_id, catalog_id) prevents the same
 * item from appearing twice in one request — quantity is handled by
 * `amount_per_item` instead.
 *
 * No `updated_at`: once a request is submitted, individual item quantities
 * are not edited (a new request is created instead).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requested_items', function (Blueprint $table) {
            $table->id();

            // The room service request this item belongs to.
            $table->foreignId('request_id')
                ->constrained('room_services')
                ->cascadeOnDelete();

            // The catalog item being requested.
            $table->foreignId('catalog_id')
                ->constrained('items_catalogs')
                ->cascadeOnDelete();

            // How many units of this item the guest wants.
            $table->unsignedSmallInteger('amount_per_item')->default(1);

            // Prevents duplicate item rows per request.
            $table->unique(['request_id', 'catalog_id']);

            // Only a creation timestamp — items are not modified after submission.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requested_items');
    }
};
