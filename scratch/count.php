<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\Booking::whereHas('room', function($q) {
    $q->where('room_type_id', 1);
})->whereIn('booking_status', ['booked', 'checked-in', 'pending'])->count();

echo "Active Bookings for Room Type 1: " . $count . "\n";
