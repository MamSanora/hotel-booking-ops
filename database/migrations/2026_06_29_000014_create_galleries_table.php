<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Galleries Table
 *
 * Stores hotel gallery images managed by admins and displayed on the
 * public-facing hotel website. Kept from the previous schema as it
 * provides value to the admin UI even though it is not part of the
 * core hotel booking operations described in the DFD.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();

            // Relative path or filename of the uploaded image.
            $table->string('image');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
