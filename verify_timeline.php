<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Guest;

echo "Checking Guest created_at distribution...\n";
$firstGuest = Guest::orderBy('created_at', 'asc')->first();
$lastGuest = Guest::orderBy('created_at', 'desc')->first();
echo "Earliest Guest: {$firstGuest->created_at}\n";
echo "Latest Guest: {$lastGuest->created_at}\n\n";

$bookings = Booking::orderBy('id', 'asc')->get(['id', 'created_at', 'check_in_date']);
if ($bookings->isEmpty()) {
    echo "Wait, there are no bookings yet. The seeder might still be running.\n";
    exit;
}

$errors = 0;
$prevDate = null;
$prevId = null;

foreach ($bookings as $b) {
    if ($prevDate !== null && $b->created_at < $prevDate) {
        echo "ERROR: ID {$b->id} ({$b->created_at}) is BEFORE ID {$prevId} ({$prevDate})\n";
        $errors++;
    }
    
    // Check if created_at is AFTER check_in_date (anomaly)
    // Walk-ins can be on the same day, so we compare dates (Y-m-d)
    $createdAtDate = $b->created_at->format('Y-m-d');
    $checkInDate = $b->check_in_date->format('Y-m-d');
    
    if ($createdAtDate > $checkInDate) {
        echo "ERROR: ID {$b->id} created ($createdAtDate) AFTER check_in ($checkInDate)\n";
        $errors++;
    }

    $prevDate = $b->created_at;
    $prevId = $b->id;
}

if ($errors === 0) {
    echo "SUCCESS: All " . $bookings->count() . " bookings are perfectly chronological and logical!\n";
} else {
    echo "Found $errors temporal anomalies.\n";
}
