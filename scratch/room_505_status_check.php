<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$room = \App\Models\Room::where('room_number', '505')->first();
if ($room) {
    dump("Room 505 current_status: " . $room->current_status);
    
    // Let's check all booking rooms regardless of status
    $allBR = \App\Models\BookingRoom::where('room_id', $room->id)->with('booking')->get();
    foreach ($allBR as $br) {
        dump("Booking Room ID: {$br->id}, Booking ID: {$br->booking_id}, Status: {$br->booking?->booking_status}");
    }
    
    // Check if the old booking relationship has anything
    $allB = $room->bookings()->get();
    foreach ($allB as $b) {
        dump("Old Relation Booking ID: {$b->id}, Status: {$b->booking_status}");
    }
}
