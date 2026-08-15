<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rt = App\Models\RoomType::find(1);
echo "RoomType 1 columns: " . PHP_EOL;
print_r($rt->toArray());

$rt2 = App\Models\RoomType::find(2);
echo "RoomType 2 columns: " . PHP_EOL;
print_r($rt2->toArray());

// Also check what columns the booking_room pivot join returns
$br = App\Models\BookingRoom::with('roomType')->find(10);
echo "BookingRoom 10 roomType name: " . ($br->roomType ? $br->roomType->name : 'NULL TYPE-ISSUE') . PHP_EOL;
echo "BookingRoom 10 roomType toArray: " . PHP_EOL;
print_r($br->roomType ? $br->roomType->toArray() : []);
