<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Livewire\Livewire::mount('reception.dashboard');
    Livewire\Livewire::mount('reception.upcoming-arrivals-list');
    echo 'SUCCESS';
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
}
