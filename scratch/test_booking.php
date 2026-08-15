<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\GuestAuth::first();
\Illuminate\Support\Facades\Auth::login($user);

$request = \Illuminate\Http\Request::create('/rooms/42/book', 'POST', [
    'check_in_date' => today()->toDateString(),
    'check_out_date' => today()->addDay()->toDateString(),
    'payment_tier' => 100,
    'payment_method' => 'khqr_aba',
    'special_requests' => 'Test',
]);

$controller = $app->make(\App\Http\Controllers\Guest\RoomController::class);

try {
    // Validate request manually since we aren't running through FormRequest in tinker easily
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'check_in_date'    => ['required', 'date', 'after_or_equal:today'],
        'check_out_date'   => ['required', 'date', 'after:check_in_date'],
        'payment_tier'     => ['required', 'integer', 'in:20,50,100'],
        'payment_method'   => ['required', 'string', 'in:khqr_aba,aba_payway'],
        'special_requests' => ['nullable', 'string', 'max:1000'],
    ]);

    if ($validator->fails()) {
        echo "Validation failed: \n";
        print_r($validator->errors()->toArray());
        exit;
    }
    
    // Bind to request
    $storeRequest = \App\Http\Requests\StoreBookingRequest::createFrom($request);
    $storeRequest->setValidator($validator);

    $room = \App\Models\Room::find(42);
    $response = $controller->store($storeRequest, $room);
    
    echo "Success! Redirecting to: " . $response->getTargetUrl() . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
