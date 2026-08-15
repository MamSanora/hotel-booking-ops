<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add Contact Info & Cleaner Role to Staff Table
 *
 * 1. Adds phone (string, nullable) and email (string, nullable) columns
 *    directly to the staff table. The industrial standard for internal
 *    staff is one primary phone and one email — no separate contact table
 *    is necessary and would only add N+1 query risk.
 *
 * 2. Expands the role enum from ['receptionist'] to ['receptionist', 'cleaner'].
 *    Cleaners get their own dashboard UI and can mark rooms as available
 *    after cleaning or maintenance supervision.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('full_name')
                ->comment('Primary contact phone number for this staff member.');
            $table->string('email', 100)->nullable()->after('phone')
                ->comment('Primary contact email for this staff member.');
        });

        // Expand the role enum. Blueprint cannot alter existing enums natively;
        // raw SQL is required for MySQL. SQLite (used in tests) ignores this.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE staff MODIFY COLUMN role ENUM('receptionist', 'cleaner') NOT NULL DEFAULT 'receptionist'");
        }
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE staff MODIFY COLUMN role ENUM('receptionist') NOT NULL DEFAULT 'receptionist'");
        }
    }
};
