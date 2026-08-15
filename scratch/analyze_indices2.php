<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function explain($query, $bindings = []) {
    echo "EXPLAIN: $query\n";
    $results = DB::select("EXPLAIN " . $query, $bindings);
    foreach ($results as $r) {
        echo "  table: {$r->table} | type: {$r->type} | possible_keys: {$r->possible_keys} | key: {$r->key} | rows: {$r->rows} | Extra: {$r->Extra}\n";
    }
    echo "\n";
}

// Create a composite index to test
DB::statement("CREATE INDEX bookings_overlapping_idx ON bookings(room_id, check_in_date, check_out_date)");

explain("SELECT * FROM rooms WHERE NOT EXISTS (
    SELECT * FROM bookings 
    WHERE rooms.id = bookings.room_id 
    AND booking_status NOT IN ('cancelled', 'no_show', 'abandoned', 'relocated', 'snatched')
    AND check_in_date < ? 
    AND check_out_date > ?
)", ['2026-08-05', '2026-08-01']);

// Drop the test index
DB::statement("DROP INDEX bookings_overlapping_idx ON bookings");
