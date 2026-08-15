<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$b = \App\Models\Booking::find(136);
echo 'Status: ' . $b->booking_status . PHP_EOL;
echo 'Total Paid: ' . $b->transactions()->whereIn('payment_status', ['full', 'partial'])->sum('amount_paid') . PHP_EOL;
echo 'Total Price: ' . $b->total_price . PHP_EOL;
