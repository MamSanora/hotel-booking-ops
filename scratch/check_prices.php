<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo \App\Models\RoomType::select('name', 'price_per_night')->get()->toJson(JSON_PRETTY_PRINT);
