<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds presentation and sizing fields to room_types.
 *
 * - size_sqm:        Fixed room size in square metres (Standard=25, Deluxe=32, Family=40, Test=null).
 * - adult_capacity:  Maximum number of adults the room type officially accommodates.
 * - child_capacity:  Maximum number of children under 12 the room type accommodates.
 *
 * The old `capacity` column (a generic integer) is kept for backward compatibility
 * but adult_capacity + child_capacity are the authoritative fields going forward.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('size_sqm')->nullable()->after('capacity');
            $table->unsignedTinyInteger('adult_capacity')->default(2)->after('size_sqm');
            $table->unsignedTinyInteger('child_capacity')->default(0)->after('adult_capacity');
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['size_sqm', 'adult_capacity', 'child_capacity']);
        });
    }
};
