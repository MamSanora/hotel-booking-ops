<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$booking = \App\Models\Booking::first();
$service = new \App\Services\KhqrService();
$result = $service->generate($booking, 10.50);

echo "KHQR String:\n" . $result['khqr_string'] . "\n\n";
echo "MD5 Hash:\n" . $result['md5_hash'] . "\n";
