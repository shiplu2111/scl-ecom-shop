<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default API Version
    |--------------------------------------------------------------------------
    |
    | This value determines the default API version used when a specific
    | version is not requested by the client.
    |
    */
    'version' => env('API_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Supported API Versions
    |--------------------------------------------------------------------------
    |
    | Here you may specify all of the API versions supported by your
    | application.
    |
    */
    'supported_versions' => [
        'v1',
    ],
];
