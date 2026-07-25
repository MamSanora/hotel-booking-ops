<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the exchange_rates table.
 *
 * Design notes:
 *   - We keep a full history of every sync, not just a single "current" row.
 *   - The latest row (ordered by fetched_at DESC) is always the active rate.
 *   - This gives the admin panel a meaningful sync history log.
 *   - source column distinguishes live API fetches from offline fallbacks.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('base', 3)->default('USD');         // source currency
            $table->char('target', 3)->default('KHR');       // target currency
            $table->decimal('rate', 12, 4);                  // e.g. 4100.0000
            $table->string('source', 50)->default('frankfurter_nbc'); // data origin
            $table->timestamp('fetched_at');                 // when the external rate was dated
            $table->timestamps();

            // Index for fast "get latest KHR/USD rate" queries
            $table->index(['base', 'target', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
