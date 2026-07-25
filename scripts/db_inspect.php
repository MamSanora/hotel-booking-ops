<?php

use App\Models\RoomType;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\PaymentGateway;

echo "=== ROOM TYPES ===" . PHP_EOL;
RoomType::all(['id','slug','display_name','price_per_night','capacity'])->each(function($rt) {
    echo $rt->id . ' | ' . $rt->slug . ' | ' . $rt->display_name . ' | $' . $rt->price_per_night . ' | cap:' . $rt->capacity . PHP_EOL;
});

echo PHP_EOL . "=== ROOM COUNTS PER TYPE ===" . PHP_EOL;
RoomType::withCount('rooms')->get()->each(function($rt) {
    echo $rt->display_name . ': ' . $rt->rooms_count . ' rooms' . PHP_EOL;
});

echo PHP_EOL . "=== PAYMENT GATEWAYS ===" . PHP_EOL;
PaymentGateway::all()->each(function($g) {
    echo $g->id . ' | ' . $g->slug . ' | ' . $g->name . ' | status:' . $g->status . PHP_EOL;
});

echo PHP_EOL . "=== BOOKING STATUS COUNTS ===" . PHP_EOL;
Booking::selectRaw('booking_status, count(*) as cnt')->groupBy('booking_status')->get()->each(function($b) {
    echo $b->booking_status . ': ' . $b->cnt . PHP_EOL;
});

echo PHP_EOL . "=== TRANSACTION METHOD COUNTS ===" . PHP_EOL;
Transaction::selectRaw('payment_method, count(*) as cnt')->groupBy('payment_method')->get()->each(function($t) {
    echo $t->payment_method . ': ' . $t->cnt . PHP_EOL;
});

echo PHP_EOL . "=== BOOKINGS TABLE COLUMNS ===" . PHP_EOL;
$cols = \Illuminate\Support\Facades\Schema::getColumnListing('bookings');
echo implode(', ', $cols) . PHP_EOL;

echo PHP_EOL . "=== ROOM_TYPES TABLE COLUMNS ===" . PHP_EOL;
$cols2 = \Illuminate\Support\Facades\Schema::getColumnListing('room_types');
echo implode(', ', $cols2) . PHP_EOL;
