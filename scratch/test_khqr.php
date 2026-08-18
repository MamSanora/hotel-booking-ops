<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = config('bakong.api_token');
$parts = explode('.', $token);
$payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)));
$exp = $payload->exp;
echo "Token expires: " . date('Y-m-d H:i:s T', $exp) . PHP_EOL;
echo "Now:           " . date('Y-m-d H:i:s T') . PHP_EOL;
echo "Expired?       " . ($exp < time() ? 'YES ⚠️' : 'NO ✅') . PHP_EOL;
echo "Days left:     " . round(($exp - time()) / 86400, 1) . PHP_EOL;
