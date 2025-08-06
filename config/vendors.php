<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Vendor API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for travel vendor APIs including authentication credentials,
    | base URLs, and vendor-specific settings.
    |
    */

    'fergusontravel' => [
        'client_id' => env('FERGUSONTRAVEL_CLIENT_ID', ''),
        'client_secret' => env('FERGUSONTRAVEL_CLIENT_SECRET', ''),
        'base_url' => env('FERGUSONTRAVEL_BASE_URL', 'https://api.fergusontravel.com/v1'),
        'timeout' => env('FERGUSONTRAVEL_TIMEOUT', 30),
        'retry_attempts' => env('FERGUSONTRAVEL_RETRY_ATTEMPTS', 3),
    ],

    'stephenstravel' => [
        'api_key' => env('STEPHENSTRAVEL_API_KEY', ''),
        'shared_secret' => env('STEPHENSTRAVEL_SHARED_SECRET', ''),
        'base_url' => env('STEPHENSTRAVEL_BASE_URL', 'https://api.stephenstravel.com/v1'),
        'timeout' => env('STEPHENSTRAVEL_TIMEOUT', 30),
        'retry_attempts' => env('STEPHENSTRAVEL_RETRY_ATTEMPTS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Vendor Settings
    |--------------------------------------------------------------------------
    */

    'default_timeout' => env('VENDOR_DEFAULT_TIMEOUT', 30),
    'default_retry_attempts' => env('VENDOR_DEFAULT_RETRY_ATTEMPTS', 3),
    'cache_ttl' => env('VENDOR_CACHE_TTL', 300), // 5 minutes
    'max_results_per_vendor' => env('VENDOR_MAX_RESULTS', 50),
];