<?php

declare(strict_types=1);

// @codeCoverageIgnoreStart
return [
    /*
    |--------------------------------------------------------------------------
    | Max Bot Access Token
    |--------------------------------------------------------------------------
    |
    | Your bot's access token from @MasterBot. This token is required for
    | authentication with the Max Bot API. You can obtain it by creating
    | a bot through @MasterBot in the Max messenger.
    |
    */
    'access_token' => env('MAXBOT_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Secret key for verifying the authenticity of webhook requests.
    | This is optional but recommended for security. Set this if you're
    | using webhooks to receive updates.
    |
    */
    'webhook_secret' => env('MAXBOT_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Max Bot API connection.
    |
    */
    'base_url' => env('MAXBOT_BASE_URL', \BushlanovDev\MaxMessengerBot\Api::API_BASE_URL),
    'api_version' => env('MAXBOT_API_VERSION'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the HTTP client used to communicate with the API.
    | All values are in seconds.
    |
    */
    'timeout' => (int)env('MAXBOT_TIMEOUT', 10),
    'connect_timeout' => (int)env('MAXBOT_CONNECT_TIMEOUT', 5),
    'read_timeout' => (int)env('MAXBOT_READ_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Whether to enable detailed logging of API requests and responses.
    | This uses Laravel's configured logger.
    |
    */
    'logging' => [
        'enabled' => (bool)env('MAXBOT_LOGGING_ENABLED', false),
        'level' => env('MAXBOT_LOGGING_LEVEL', 'debug'),
    ],
];
// @codeCoverageIgnoreEnd
