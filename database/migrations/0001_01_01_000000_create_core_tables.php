<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core Laravel Infrastructure Tables
 *
 * Creates all standard Laravel framework tables required for sessions,
 * password resets, caching, queue jobs, and notifications.
 *
 * NOTE: This replaces the old create_users_table migration. The `users` table
 * no longer exists in this schema. Guests authenticate via `guest_auths`.
 * The `password_reset_tokens` table is used for guest password reset emails.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Password Reset Tokens ────────────────────────────────────────────
        // Used when a registered guest (via guest_auths.email) requests a
        // password reset link. Indexed by email for fast lookup.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ── Sessions ─────────────────────────────────────────────────────────
        // Stores all active user sessions (guests, admins, staff).
        // `user_id` is nullable and NOT a foreign key — multi-guard auth means
        // sessions can belong to any authenticatable model (GuestAuth, Admin,
        // Staff), so a strict FK constraint is intentionally omitted here.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ── Cache ─────────────────────────────────────────────────────────────
        // Database-backed cache store (configured via CACHE_STORE=database in .env).
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // ── Queue Jobs ────────────────────────────────────────────────────────
        // Database-backed queue driver (configured via QUEUE_CONNECTION=database).
        // Used for queuing notifications, emails, and other async tasks.
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // ── Notifications ─────────────────────────────────────────────────────
        // Stores database notifications for any notifiable model
        // (GuestAuth, Admin, Staff). `notifiable` is a polymorphic relation.
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
    }
};
