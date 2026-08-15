<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Update Family Triple Room Adult Capacity
 *
 * The Family Triple Room physically contains three single beds — meaning it
 * can accommodate 3 adults. However, the original seeder set adult_capacity
 * to 2 (matching a "2 adults + 2 children under 12" description).
 *
 * Per the new codebase rule: 3 children = 1 adult equivalent for room
 * allocation purposes. The Family Triple Room must reflect its true
 * physical adult capacity of 3.
 *
 * This is a DATA MIGRATION (not a seeder change) so that developers who
 * pull from GitHub only need to run `php artisan migrate` — their existing
 * database row is safely updated without losing any other data.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('room_types')
            ->where('slug', 'family_triple_room')
            ->update(['adult_capacity' => 3]);
    }

    public function down(): void
    {
        DB::table('room_types')
            ->where('slug', 'family_triple_room')
            ->update(['adult_capacity' => 2]);
    }
};
