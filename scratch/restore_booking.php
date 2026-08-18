<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = \App\Models\Booking::find(462);
$booking->booking_status = \App\Models\Booking::STATUS_PENDING;
$booking->save();

app(\App\Services\AbaTelegramService::class)->promoteBookingAfterPayment($booking);

echo 'Booking status is now: ' . $booking->fresh()->booking_status . PHP_EOL;
