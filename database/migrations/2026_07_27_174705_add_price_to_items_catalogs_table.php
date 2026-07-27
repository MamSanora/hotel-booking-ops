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
        Schema::table('items_catalogs', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(0)->after('category');
        });

        // Delete the requested rows
        DB::table('items_catalogs')->whereIn('item_name', ['Hypoallergenic Pillow', 'Fruit Basket'])->delete();

        // Update beverage prices (USD)
        $beverages = [
            'Bottled Water (500ml)' => 0.25,  // ~1000 KHR
            'Bottled Water (1.5L)'  => 0.50,  // ~2000 KHR
            'Hot Green Tea'         => 0.50,
            'Hot Coffee'            => 0.75,
            'Orange Juice'          => 1.00,
            'Coca-Cola (Can)'       => 0.60,  // 2500 KHR approx
            'Local Beer (Can)'      => 1.50,
        ];

        foreach ($beverages as $name => $price) {
            DB::table('items_catalogs')->where('item_name', $name)->update(['price' => $price]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items_catalogs', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
