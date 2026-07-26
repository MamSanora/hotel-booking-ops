<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->enum('bed_configuration', ['twin', 'double', 'triple'])
                ->nullable()
                ->after('current_status')
                ->comment('Physical bed layout of the room');

            $table->enum('view_type', ['balcony', 'window', 'none'])
                ->default('none')
                ->after('bed_configuration')
                ->comment('Physical view type of the room');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['bed_configuration', 'view_type']);
        });
    }
};
