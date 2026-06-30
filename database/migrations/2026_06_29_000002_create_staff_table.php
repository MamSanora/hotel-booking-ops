<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff Table
 *
 * Stores front-desk staff (receptionist) accounts. Replaces the old
 * `receptionists` table. Staff authenticate with a username and passwordhash.
 *
 * Each staff member is optionally linked to the admin who manages them.
 * If that admin is deleted, the staff record is preserved (SET NULL).
 *
 * Authenticated via the 'staff' guard (see config/auth.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();

            $table->string('full_name', 50);

            // Currently only 'receptionist' is supported. The enum is defined
            // here for forward-compatibility if new roles are added later.
            // Empty strings removed from SQL dump — phpMyAdmin artifact.
            $table->enum('role', ['receptionist'])->default('receptionist');

            // The admin responsible for this staff member. Nullable so that
            // staff accounts survive the deletion of their managing admin.
            $table->foreignId('managed_by_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->string('username', 50)->unique();

            // Named `passwordhash` to match the SQL schema. The Staff model
            // overrides getAuthPassword() to return this column name.
            $table->string('passwordhash');

            // Required by Laravel's Authenticatable contract for "remember me".
            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
