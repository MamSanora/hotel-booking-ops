<?php
use App\Models\Room;
use App\Models\RoomType;

// Verify room distribution
echo "=== ROOM DISTRIBUTION BY NEW TYPES ===\n";
$types = RoomType::whereIn('slug', ['standard_room', 'deluxe_room', 'family_triple_room', 'test_room'])
    ->withCount('rooms')->get();

foreach ($types as $t) {
    echo $t->display_name . ': ' . $t->rooms_count . " rooms | \${$t->price_per_night}/nt | {$t->size_sqm}m²\n";
}

echo "\n=== SAMPLE ROOMS (first 5 per type) ===\n";
foreach ($types as $t) {
    $rooms = Room::where('room_type_id', $t->id)->take(5)->pluck('room_number')->toArray();
    echo $t->slug . ': ' . implode(', ', $rooms) . "\n";
}

echo "\n=== OLD TYPE ROOMS STILL IN DB ===\n";
$old = RoomType::whereIn('slug', ['standard_twin', 'standard_double', 'deluxe_double'])->withCount('rooms')->get();
foreach ($old as $t) {
    echo $t->display_name . ': ' . $t->rooms_count . " rooms (OLD)\n";
}

echo "\n=== PAYMENT GATEWAYS ===\n";
App\Models\PaymentGateway::all()->each(fn($g) => print($g->id.' | '.$g->slug.' | '.$g->admin_status."\n"));
