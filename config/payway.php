<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ABA PayWay Configuration
    |--------------------------------------------------------------------------
    |
    | Stores merchant credentials and API configuration for ABA PayWay integration.
    | Credentials are loaded securely from environment variables (.env).
    |
    */

    'merchant_id' => env('ABA_PAYWAY_MERCHANT_ID', 'demo_merchant'),
    'api_key'     => env('ABA_PAYWAY_API_KEY', 'demo_api_key'),
    'api_url'     => env('ABA_PAYWAY_API_URL', 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase'),
    'currency'    => env('ABA_PAYWAY_CURRENCY', 'USD'),
];
