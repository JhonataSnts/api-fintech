<?php

return [
    'env' => env('PAGSEGURO_ENV', 'sandbox'),

    'sandbox' => [
        'email' => env('PAGSEGURO_EMAIL_SANDBOX'),
        'token' => env('PAGSEGURO_TOKEN_SANDBOX'),
        'base_url' => env('PAGSEGURO_API_URL', 'https://sandbox.api.pagseguro.com'),
    ],
];