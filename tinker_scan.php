<?php
$roomTypes = \App\Models\RoomType::all(['id', 'display_name']);
echo "Room Types:\n";
foreach ($roomTypes as $rt) {
    echo "ID: {$rt->id} | Name: {$rt->display_name}\n";
}

$rooms = \App\Models\Room::take(5)->get(['id', 'room_number', 'room_type_id']);
echo "\nRooms (first 5):\n";
foreach ($rooms as $r) {
    echo "ID: {$r->id} | Room: {$r->room_number} | Type ID: {$r->room_type_id}\n";
}
