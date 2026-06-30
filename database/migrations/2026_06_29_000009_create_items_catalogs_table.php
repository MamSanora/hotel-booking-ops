<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Items Catalogs Table
 *
 * Stores the hotel's catalog of requestable items that guests can include
 * in a room service request. Items are organised by category.
 *
 * Admins create and manage catalog items. If the creating admin is deleted,
 * the catalog item is preserved (SET NULL) since it has operational value
 * independent of who created it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_catalogs', function (Blueprint $table) {
            $table->id();

            $table->string('item_name', 50);

            // Item category for grouping in the room service request form.
            // Empty string removed from SQL dump — phpMyAdmin artifact.
            $table->enum('category', ['amenity', 'bedding', 'beverage'])
                ->nullable();

            // Records which admin added this item to the catalog.
            // Preserved (SET NULL) if that admin account is deleted.
            $table->foreignId('created_by_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_catalogs');
    }
};
