<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$charges = \App\Models\IncidentalCharge::where('booking_id', 403)->where('room_id', 12)->get();
foreach($charges as $charge) {
    $charge->update(['booking_id' => 336]);
}
\App\Models\Booking::find(403)->decrement('total_price', $charges->sum('total_amount'));
\App\Models\Booking::find(336)->increment('total_price', $charges->sum('total_amount'));

echo "Done\n";
