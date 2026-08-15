<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$b = \App\Models\Booking::find(136);
if ($b) {
    $b->update(['booking_status' => 'checked-out']);
    $b->room->update(['current_status' => 'available']);
    echo 'Cleaned up';
}
