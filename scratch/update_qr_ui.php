<?php

$src = 'd:/xampp/htdocs/Project_Sarana/Hotel_Booking_Ops/resources/views/payment/payway-static-qr.blade.php';
$dest = 'd:/xampp/htdocs/Project_Sarana/Hotel_Booking_Ops/resources/views/payment/qr.blade.php';

$content = file_get_contents($src);

// Replace the khqr string variable
$content = str_replace('@json($khqrString)', "@json(\$khqrData['khqr_string'])", $content);

// Update title just in case
$content = str_replace('Complete Payment &mdash; KHQR / ABA Pay', 'Complete Payment &mdash; Bakong Open API', $content);

file_put_contents($dest, $content);
echo "qr.blade.php updated with payway-static-qr UI!\n";
