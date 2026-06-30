<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admins Table
 *
 * Stores hotel administrator accounts. Admins authenticate with a username
 * and passwordhash (not email). The `role` column distinguishes between
 * a superadmin (full access) and a regular admin (standard access).
 *
 * Authenticated via the 'admin' guard (see config/auth.php).
 * This table is completely separate from guests and staff.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->string('full_name', 50);

            // Differentiates superadmin (unrestricted) from regular admin.
            // Empty string removed from SQL dump — it was a phpMyAdmin artifact.
            $table->enum('role', ['superadmin', 'admin'])->default('admin');

            $table->string('username', 50)->unique();

            // Named `passwordhash` to match the SQL schema. The Admin model
            // overrides getAuthPassword() to return this column name.
            $table->string('passwordhash');

            // Required by Laravel's Authenticatable contract for "remember me"
            // sessions. Not in the SQL dump but necessary for auth to work.
            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
