<?php

return [
    'account' => env('OCTANY_ACCOUNT'),

    'api_key' => env('OCTANY_API_KEY'),

    'api_url' => env('OCTANY_API_URL'),

    'webhook_secret' => env('OCTANY_WEBHOOK_SECRET'),

    'http_options' => [
        // Passed straight to Laravel's HTTP client via withOptions().
        // Useful for local dev against self-signed certs:
        // 'verify' => env('OCTANY_VERIFY_SSL', true),
    ],

    'log' => [
        'requests' => true,
        'channel' => env('LOG_CHANNEL'),
    ],
];
