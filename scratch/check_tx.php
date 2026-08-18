<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = \App\Models\Transaction::where('payment_method', '!=', 'cash')->latest()->first();
echo "ID: " . $t->id . "\n";
echo "KHQR: " . $t->khqr_string . "\n";
echo "Method: " . $t->payment_method . "\n";
