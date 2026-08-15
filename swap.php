<?php
$file = 'd:\\xampp\\htdocs\\Project_Sarana\\Hotel_Booking_Ops\\resources\\views\\reception\\dashboard.blade.php';
$lines = file($file);

// 0-indexed: lines 120 to 218 is Pending Room Service
// lines 219 to 309 is Today's Guest Movement
// Actually, let's just find the indices to be absolutely sure!

$start_pending = -1;
$end_pending = -1;
$start_today = -1;
$end_today = -1;

foreach ($lines as $i => $line) {
    if (strpos($line, 'PENDING ROOM SERVICE REQUESTS (alert panel)') !== false) {
        $start_pending = $i - 1; // get the previous comment line
    }
    if (strpos($line, 'TODAY\'S GUEST MOVEMENT (mini cards)') !== false) {
        $start_today = $i - 1;
        // The end of pending is right before $start_today
        $end_pending = $start_today - 1;
    }
    if (strpos($line, 'TABBED OPERATIONS PANEL') !== false) {
        $end_today = $i - 2; // the blank line before TABBED OPERATIONS
        break;
    }
}

echo "Start Pending: $start_pending, End Pending: $end_pending\n";
echo "Start Today: $start_today, End Today: $end_today\n";

if ($start_pending !== -1 && $start_today !== -1 && $end_today !== -1) {
    $before = array_slice($lines, 0, $start_pending);
    $pending = array_slice($lines, $start_pending, $end_pending - $start_pending + 1);
    $today = array_slice($lines, $start_today, $end_today - $start_today + 1);
    $after = array_slice($lines, $end_today + 1);
    
    // Swap!
    $new_lines = array_merge($before, $today, ["\n"], $pending, $after);
    file_put_contents($file, implode("", $new_lines));
    echo "Swapped successfully!\n";
} else {
    echo "Failed to find blocks.\n";
}
