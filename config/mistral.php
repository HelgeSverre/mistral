<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mistral API Key
    |--------------------------------------------------------------------------
    |
    | Your Mistral API key is used to authenticate requests made from your
    | application to the Mistral.ai services. You can generate and manage
    | your API keys in the Mistral.ai dashboard.
    |
    */

    'api_key' => env('MISTRAL_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Mistral Base URL
    |--------------------------------------------------------------------------
    |
    | This URL is the base endpoint for all Mistral.ai API requests. While it's
    | set to Mistral's default API server, you can change it for self-hosted
    | models or different test environments (if applicable, in the future).
    |
    */

    'base_url' => env('MISTRAL_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Mistral Timeout
    |--------------------------------------------------------------------------
    |
    | This configuration option defines the maximum duration (in seconds) that
    | your application will wait for a response when making requests to the
    | Mistral.ai API. By default, this value is set to 60 seconds. If you wish
    | to disable the timeout entirely, you can set this value to 0.
    |
    */

    'timeout' => env('MISTRAL_TIMEOUT', 60),
];
