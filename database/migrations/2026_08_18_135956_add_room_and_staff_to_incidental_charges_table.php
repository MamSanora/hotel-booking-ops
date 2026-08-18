<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add room_id and reported_by_staff_id to incidental_charges.
 *
 * room_id:
 *   Links a damage charge to the specific physical room it occurred in.
 *   This is critical for multi-room bookings (e.g. a family booking 3 rooms)
 *   so the receptionist can say "Broken glass in Room 207, not Room 208".
 *   Nullable: some charges (e.g. late check-out fee) apply to the booking
 *   as a whole, not a specific room.
 *
 * reported_by_staff_id:
 *   Audit trail — records which cleaner or staff member submitted the charge.
 *   Protects both staff and hotel if a damage report is disputed by the guest.
 *   Nullable: charges entered directly by the receptionist at the desk may
 *   not have an explicit staff reporter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidental_charges', function (Blueprint $table) {
            // Which specific room the damage occurred in.
            $table->foreignId('room_id')
                ->nullable()
                ->after('booking_id')
                ->constrained('rooms')
                ->nullOnDelete();

            // Which staff member (cleaner or receptionist) reported the damage.
            $table->foreignId('reported_by_staff_id')
                ->nullable()
                ->after('transaction_id')
                ->constrained('staff')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incidental_charges', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropForeign(['reported_by_staff_id']);
            $table->dropColumn(['room_id', 'reported_by_staff_id']);
        });
    }
};
