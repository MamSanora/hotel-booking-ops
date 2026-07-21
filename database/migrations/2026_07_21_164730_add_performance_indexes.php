<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds performance indexes identified by query analysis across all models
 * and controllers. Every index here corresponds to a real WHERE clause
 * executing on every page load or critical user action.
 *
 * Tables affected:
 *  - bookings       : check_in_date/check_out_date (availability), booking_status (scopes)
 *  - transactions   : payment_status (payment polling & callbacks)
 *  - room_services  : request_status (pending service queue)
 *  - rooms          : current_status (availability filtering)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── bookings ─────────────────────────────────────────────────────────
        Schema::table('bookings', function (Blueprint $table) {
            // Composite index for date-range overlap queries.
            // Used in: Room::isBookedDuring(), RoomType::countActiveBookings(),
            //          RoomController conflict check, ReceptionDashboard conflict check.
            // Pattern: WHERE check_in_date < ? AND check_out_date > ?
            $table->index(['check_in_date', 'check_out_date'], 'bookings_date_range_index');

            // Used by every query scope in Booking.php:
            // scopePending, scopeBooked, scopeCheckedIn, scopeCheckedOut,
            // scopeActive, scopeArrivingToday, scopeUpcomingArrivals, scopeDepartingToday.
            $table->index('booking_status', 'bookings_status_index');
        });

        // ── transactions ──────────────────────────────────────────────────────
        Schema::table('transactions', function (Blueprint $table) {
            // Used in PaymentController (pending transaction polling) and
            // Transaction::scopePending(). Critical path: every payment page load.
            $table->index('payment_status', 'transactions_payment_status_index');
        });

        // ── room_services ─────────────────────────────────────────────────────
        Schema::table('room_services', function (Blueprint $table) {
            // Used by RoomService::scopePending() which drives the staff
            // pending-service queue on the reception dashboard.
            $table->index('request_status', 'room_services_request_status_index');
        });

        // ── rooms ─────────────────────────────────────────────────────────────
        Schema::table('rooms', function (Blueprint $table) {
            // Used by Room::scopeAvailable(), Room::scopeOccupied(), and
            // RoomType::getAvailableRoomsCount() which both call
            // ->where('current_status', ...) on every availability check.
            $table->index('current_status', 'rooms_current_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_date_range_index');
            $table->dropIndex('bookings_status_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_payment_status_index');
        });

        Schema::table('room_services', function (Blueprint $table) {
            $table->dropIndex('room_services_request_status_index');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('rooms_current_status_index');
        });
    }
};
