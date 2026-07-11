<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bakong Account ID
    |--------------------------------------------------------------------------
    |
    | Your personal Bakong Account ID — typically your phone number followed
    | by your bank's identifier code (e.g. "012345678@aba", "012345678@wing").
    | This is the payee account embedded in every KHQR code the system generates.
    |
    */
    'account_id'    => env('BAKONG_ACCOUNT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Merchant Display Name & City
    |--------------------------------------------------------------------------
    |
    | Shown to the payer inside their banking app when they scan the QR.
    | Keep this recognizable (e.g. your hotel name).
    |
    */
    'merchant_name' => env('BAKONG_MERCHANT_NAME', 'Hotel Sarana'),
    'merchant_city' => env('BAKONG_MERCHANT_CITY', 'Phnom Penh'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Currency
    |--------------------------------------------------------------------------
    |
    | ISO 4217 numeric currency code:
    |   '840' = USD (US Dollar)
    |   '116' = KHR (Cambodian Riel)
    |
    */
    'currency' => env('BAKONG_CURRENCY', '840'),

    /*
    |--------------------------------------------------------------------------
    | Bakong Open API
    |--------------------------------------------------------------------------
    |
    | Base URL and Bearer token for the Bakong Open API.
    | Register your email at https://api-bakong.nbc.gov.kh to get a token.
    |
    */
    'api_url'   => env('BAKONG_API_URL', 'https://api-bakong.nbc.gov.kh'),
    'api_token' => env('BAKONG_API_TOKEN', ''),
];
