<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/reception/walk-in', 'POST', [
    'full_name' => 'Walk In Guest',
    'phone_number' => '012345678',
    'room_id' => 42,
    'check_in_date' => today()->toDateString(),
    'check_out_date' => today()->addDay()->toDateString(),
    'payment_tier' => 100,
    'payment_method' => 'cash',
]);

$controller = $app->make(\App\Http\Controllers\Reception\WalkInBookingController::class);

try {
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'full_name'       => ['required', 'string', 'max:255'],
        'phone_number'     => ['required', 'string', 'max:20'],
        'room_id'          => ['required', 'exists:rooms,id'],
        'check_in_date'    => ['required', 'date', 'after_or_equal:today'],
        'check_out_date'   => ['required', 'date', 'after:check_in_date'],
        'payment_tier'     => ['required', 'integer', 'in:20,50,100'],
        'payment_method'   => ['required', 'string', 'in:cash,credit_card,bank_transfer'],
    ]);

    if ($validator->fails()) {
        echo "Validation failed: \n";
        print_r($validator->errors()->toArray());
        exit;
    }
    
    // Bind to request
    $storeRequest = \App\Http\Requests\StoreWalkInBookingRequest::createFrom($request);
    $storeRequest->setValidator($validator);

    // Mock staff login
    $staff = \App\Models\Staff::first();
    \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn((object)['staff_id' => $staff->id]);
    \Illuminate\Support\Facades\Auth::shouldReceive('id')->andReturn($staff->id);

    $response = $controller->store($storeRequest);
    
    echo "Success! Redirecting to: " . $response->getTargetUrl() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
