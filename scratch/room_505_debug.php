<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roomId = \App\Models\Room::where('room_number', '505')->value('id');
dump("Room ID: " . $roomId);

$bookingRooms = \App\Models\BookingRoom::where('room_id', $roomId)
    ->whereHas('booking', fn($q) => $q->whereIn('booking_status', ['checked-in', 'checked-out']))
    ->get();

dump("Booking Rooms count: " . $bookingRooms->count());
if ($bookingRooms->count() > 0) {
    dump($bookingRooms->toArray());
}

$allBookingRooms = \App\Models\BookingRoom::where('room_id', $roomId)->get();
dump("All Booking Rooms for this room: " . $allBookingRooms->count());
foreach($allBookingRooms as $br) {
    dump("BR ID: {$br->id}, Booking Status: {$br->booking?->booking_status}");
}
