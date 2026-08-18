<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use KHQR\Models\IndividualInfo;
use KHQR\BakongKHQR;

$individualInfo = new IndividualInfo(
    "ny_sokchansoursdey@bkrt",
    "DARAMEAS HOTEL",
    "Phnom Penh",
);

$amount = 10.50;
$currency = "840"; // USD
$billNumber = "BK-000001";
$terminalLabel = "";

$bakongKHQR = new BakongKHQR(config('bakong.api_token'));
$response = $bakongKHQR->generateIndividual($individualInfo, $amount, $currency, $billNumber, $terminalLabel);

echo "Library JSON:\n" . $response . "\n\n";

$booking = \App\Models\Booking::first();
$service = new \App\Services\KhqrService();
$result = $service->generate($booking, 10.50);

echo "KhqrService:\n" . $result['khqr_string'] . "\n\n";
