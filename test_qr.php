<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qrString = \App\Services\KhqrGenerator::forAmount(0.08, 'BK-000141');
$qrImageBase64 = (new \chillerlan\QRCode\QRCode)->render($qrString);

echo substr($qrImageBase64, 0, 100) . "\n";
