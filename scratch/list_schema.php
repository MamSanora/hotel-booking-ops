<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::select("SHOW TABLES");
foreach ($tables as $t) {
    $table = array_values((array)$t)[0];
    echo "TABLE: {$table}\n";
    foreach(DB::select("SHOW COLUMNS FROM {$table}") as $col) {
        echo "  - {$col->Field} ({$col->Type})\n";
    }
    echo "  INDICES:\n";
    foreach(DB::select("SHOW INDEX FROM {$table}") as $idx) {
        echo "    - {$idx->Key_name} on {$idx->Column_name}\n";
    }
    echo "\n";
}
