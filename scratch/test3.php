<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type = App\Models\RoomType::where('slug', 'standard_room')->first();
if (!$type) {
    echo "RoomType not found.\n";
    exit;
}

$room = $type->rooms()->where('current_status', '!=', 'maintenance')->first();
echo "Room found: " . ($room ? "YES, ID: " . $room->id : "NO") . "\n";
