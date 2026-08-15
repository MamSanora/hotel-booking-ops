<?php
$bookingsSchema = Illuminate\Support\Facades\Schema::getColumnListing('bookings');
echo 'Bookings columns: ' . implode(', ', $bookingsSchema) . "\n";

// check the guest_type enum values in the db if it's enum
$type = DB::select("SHOW COLUMNS FROM bookings WHERE Field = 'guest_type'")[0]->Type;
echo "Guest Type column type: " . $type . "\n";
