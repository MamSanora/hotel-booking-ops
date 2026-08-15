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

// 1. Room overlapping bookings check
explain("SELECT * FROM rooms WHERE NOT EXISTS (
    SELECT * FROM bookings 
    WHERE rooms.id = bookings.room_id 
    AND booking_status NOT IN ('cancelled', 'no_show', 'abandoned', 'relocated', 'snatched')
    AND check_in_date < ? 
    AND check_out_date > ?
)", ['2026-08-05', '2026-08-01']);

// 2. Transaction Amount Collision check
explain("SELECT * FROM transactions 
    WHERE payment_status = 'pending' 
    AND payment_method IN ('khqr_aba', 'khqr', 'aba_telegram') 
    AND amount_paid = 0.20 
    AND updated_at >= ?
", [now()->subMinute()->toDateTimeString()]);

// 3. Admin active payment gateways
explain("SELECT * FROM payment_gateways WHERE admin_status = 'active'");

// 4. Room Types visibility
explain("SELECT * FROM room_types WHERE is_visible = 1");

// 5. Booking pending check (for cron job)
explain("SELECT * FROM bookings WHERE booking_status = 'pending' AND created_at < ?", [now()->subMinutes(15)->toDateTimeString()]);

