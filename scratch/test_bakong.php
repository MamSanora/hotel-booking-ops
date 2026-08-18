<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiToken = config('bakong.api_token');
$apiUrl = rtrim(config('bakong.api_url'), '/');
$accountId = config('bakong.account_id');

$response = \Illuminate\Support\Facades\Http::withToken($apiToken)
    ->post("{$apiUrl}/v1/check_bakong_account", [
        'accountId' => $accountId
    ]);

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";
