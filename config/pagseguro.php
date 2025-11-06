<?php
return [
    'env' => env('PAGSEGURO_ENV', 'sandbox'),
    'sandbox' => [
        'app_id'  => env('PAGSEGURO_APP_ID'),
        'app_key' => env('PAGSEGURO_APP_KEY'),
        'base_url'=> env('PAGSEGURO_API_URL', 'https://sandbox.api.pagseguro.com'),
    ],
];