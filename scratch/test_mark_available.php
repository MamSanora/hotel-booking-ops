<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = \App\Models\Room::find(42);
$r->update(['current_status' => 'cleaning']);

$request = \Illuminate\Http\Request::create('/reception/room-check/42/mark-available', 'PATCH');
$controller = $app->make(\App\Http\Controllers\Reception\RoomCheckController::class);

$staff = \App\Models\Staff::first();
\Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn((object)['staff_id' => $staff->id]);
\Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn($staff->id);

try {
    $response = $controller->markAvailable($request, $r);
    $r->refresh();
    echo "Status after controller: " . $r->current_status . "\n";
    echo "Session success: " . session('success') . "\n";
    echo "Session error: " . session('error') . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
