<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = App\Models\Booking::find(141);
if (!$booking) {
    echo "Booking not found\n";
} else {
    $hasPaid = $booking->transactions()->whereIn('payment_status', ['full', 'partial'])->exists();
    echo "Has paid? " . ($hasPaid ? 'yes' : 'no') . "\n";
    echo "Is Refundable? " . ($booking->isRefundable() ? 'yes' : 'no') . "\n";
}
