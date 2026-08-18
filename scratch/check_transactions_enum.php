<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$columns = DB::select("SHOW COLUMNS FROM transactions WHERE Field = 'payment_status'");
print_r($columns);
