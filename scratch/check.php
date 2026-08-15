<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$available = \App\Models\Room::availableForDates(today()->toDateString(), today()->addDay()->toDateString())->pluck('id')->toArray();
echo "Available Room IDs: " . implode(', ', $available) . "\n";
