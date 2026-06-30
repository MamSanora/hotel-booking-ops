<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phones Table
 *
 * Stores one or more phone numbers per guest. Separated into its own table
 * to allow guests to have multiple contact numbers (e.g. personal and work).
 *
 * Cascade delete: removing a guest removes all their phone records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guest_id')
                ->constrained('guests')
                ->cascadeOnDelete();

            $table->string('phone_number');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
