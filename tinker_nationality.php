<?php
// Check guests table structure
$columns = Illuminate\Support\Facades\Schema::getColumnListing('guests');
echo "Columns in guests table:\n";
print_r($columns);

// Check distinct nationalities
$nationalities = App\Models\Guest::select('nationality')->distinct()->pluck('nationality')->toArray();
echo "\nDistinct nationalities:\n";
print_r($nationalities);
