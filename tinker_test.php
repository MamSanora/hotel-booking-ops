<?php
$start = now()->subDays(6)->startOfDay();
$end = now()->endOfDay();
$rawBookings = \App\Models\Booking::whereBetween('bookings.created_at', [$start, $end])
    ->selectRaw('DATE(bookings.created_at) as period_key, COUNT(*) as total')
    ->groupByRaw('DATE(bookings.created_at)')
    ->pluck('total', 'period_key')
    ->toArray();
echo "\n---RAW_BOOKINGS---\n";
echo json_encode($rawBookings);
echo "\n------------------\n";
