<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('room_management', 'room_type_audit_logs');

        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::drop('room_type_audit_logs');
            Schema::create('room_type_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
                $table->foreignId('managed_by_admin_id')->constrained('admins')->cascadeOnDelete();
                $table->string('action', 50);
                $table->timestamp('created_at')->useCurrent();
            });
            return;
        }

        Schema::disableForeignKeyConstraints();
        Schema::table('room_type_audit_logs', function (Blueprint $table) {
            $table->dropForeign('room_management_room_id_foreign');
            $table->dropColumn('room_id');
            
            $table->foreignId('room_type_id')->after('id')->constrained('room_types')->cascadeOnDelete();
            
            // Change enum to string so we can add new actions like 'add_room_type'
            $table->string('action', 50)->change();
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_type_audit_logs', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['room_type_id']);
            }
            $table->dropColumn('room_type_id');
            
            $table->foreignId('room_id')->after('id')->constrained('rooms')->cascadeOnDelete();
        });

        Schema::rename('room_type_audit_logs', 'room_management');
    }
};
