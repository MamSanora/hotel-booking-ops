<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalize Rooms: Replace Redundant Columns with room_type_id FK
 *
 * Steps performed by this migration:
 *
 *  1. Seed the `room_types` table with the three existing types and their
 *     current prices/capacities/descriptions pulled directly from the live
 *     `rooms` data, so no information is lost.
 *
 *  2. Add `room_type_id` (nullable FK) to `rooms`.
 *
 *  3. For every room row, set `room_type_id` by matching `rooms.room_type`
 *     to the corresponding `room_types.slug`.
 *
 *  4. Drop the nullOnDelete constraint and re-add with RESTRICT, then make
 *     `room_type_id` NOT NULL.
 *
 *  5. Drop the now-redundant columns: `room_type`, `price_per_night`,
 *     `capacity`, `description`.
 *
 * The `down()` method fully reverses this, restoring the original columns
 * from the room_types data so the database is not left in a broken state
 * on rollback.
 *
 * Note: nullOnDelete() and NOT NULL are mutually exclusive in MySQL InnoDB.
 * We use restrictOnDelete() so that a room_type cannot be deleted while
 * rooms reference it — the correct semantic for this relationship.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Step 1: Seed room_types from the existing rooms data ─────────────
        $existingTypes = DB::table('rooms')
            ->select('room_type', 'price_per_night', 'capacity', 'description')
            ->whereNotNull('room_type')
            ->groupBy('room_type', 'price_per_night', 'capacity', 'description')
            ->get();

        $displayNames = [
            'standard_twin'   => 'Standard Twin',
            'standard_double' => 'Standard Double',
            'deluxe_double'   => 'Deluxe Double',
            'family_room'     => 'Family Room',
            'suite'           => 'Suite',
        ];

        foreach ($existingTypes as $type) {
            DB::table('room_types')->insertOrIgnore([
                'slug'            => $type->room_type,
                'display_name'    => $displayNames[$type->room_type] ?? ucfirst(str_replace('_', ' ', $type->room_type)),
                'capacity'        => $type->capacity ?? 2,
                'price_per_night' => $type->price_per_night ?? 0,
                'description'     => $type->description,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // ── Step 2: Add nullable FK to rooms ─────────────────────────────────
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('room_type_id')
                ->nullable()
                ->after('room_number')
                ->constrained('room_types')
                ->nullOnDelete();  // Temporary — changed to RESTRICT after data is populated.
        });

        // ── Step 3: Populate the FK for every existing room ──────────────────
        $roomTypes = DB::table('room_types')->pluck('id', 'slug');
        foreach ($roomTypes as $slug => $id) {
            DB::table('rooms')->where('room_type', $slug)->update(['room_type_id' => $id]);
        }

        // ── Step 4: Swap constraint to RESTRICT, then make NOT NULL ──────────
        // MySQL cannot make a nullOnDelete FK column NOT NULL, so we drop and
        // re-add the constraint with RESTRICT before changing nullability.
        DB::statement('ALTER TABLE rooms DROP FOREIGN KEY rooms_room_type_id_foreign');
        DB::statement('ALTER TABLE rooms MODIFY room_type_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE rooms ADD CONSTRAINT rooms_room_type_id_foreign FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE RESTRICT');

        // ── Step 5: Drop the redundant columns ───────────────────────────────
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['room_type', 'price_per_night', 'capacity', 'description']);
        });
    }

    public function down(): void
    {
        // Restore the original columns from room_types data.
        Schema::table('rooms', function (Blueprint $table) {
            $table->enum('room_type', [
                'standard_twin', 'standard_double', 'deluxe_double', 'family_room', 'suite',
            ])->nullable()->after('current_status');
            $table->decimal('price_per_night', 10, 2)->nullable()->after('room_type');
            $table->unsignedInteger('capacity')->nullable()->after('price_per_night');
            $table->text('description')->nullable()->after('capacity');
        });

        // Re-populate restored columns from room_types via the FK.
        $types = DB::table('room_types')->get()->keyBy('id');
        DB::table('rooms')->get()->each(function ($room) use ($types) {
            $type = $types[$room->room_type_id] ?? null;
            if (! $type) return;
            DB::table('rooms')->where('id', $room->id)->update([
                'room_type'       => $type->slug,
                'price_per_night' => $type->price_per_night,
                'capacity'        => $type->capacity,
                'description'     => $type->description,
            ]);
        });

        // Drop the FK column.
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_type_id');
        });
    }
};
