<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::find(140);
echo "Now: " . now()->toDateTimeString() . "\n";
echo "Cancelled at: " . $booking->updated_at->toDateTimeString() . "\n";
echo "Check in date: " . $booking->check_in_date . "\n";
$checkInDateTime = \Carbon\Carbon::parse($booking->check_in_date->format('Y-m-d') . ' 14:00:00');
echo "Check in datetime obj: " . $checkInDateTime->toDateTimeString() . "\n";
echo "SubDay: " . $checkInDateTime->subDay()->toDateTimeString() . "\n";
