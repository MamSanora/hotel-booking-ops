<?php
$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE', 'laravel');
$key = 'Tables_in_' . $dbName;
foreach ($tables as $table) {
    $tableName = $table->{$key} ?? current((array)$table);
    $count = DB::table($tableName)->count();
    echo str_pad($tableName, 30) . ": " . $count . " rows\n";
}
