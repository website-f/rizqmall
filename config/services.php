<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sandbox System (Main Auth & Profile Microservice)
    |--------------------------------------------------------------------------
    */

    'sandbox' => [
        'url' => env('SANDBOX_URL', 'http://localhost:8000'),
        'base_url' => env('SANDBOX_BASE_URL', 'https://rm.sandboxmalaysia.com'),
        'api_key' => env('SANDBOX_API_KEY'),
        'timeout' => env('SANDBOX_TIMEOUT', 10),

        // Webhook settings
        'webhook_secret' => env('SANDBOX_WEBHOOK_SECRET'),

        // SSO settings
        'sso_enabled' => env('SANDBOX_SSO_ENABLED', true),
        'sso_secret' => env('SANDBOX_SSO_SECRET'),
    ],

    // Alias for backward compatibility with views
    'subscription' => [
        'base_url' => env('SANDBOX_URL', 'http://localhost:8000'),
    ],

    'sso' => [
        'token_expiry' => env('SSO_TOKEN_EXPIRY', 3600), // 1 hour
        'session_lifetime' => env('SSO_SESSION_LIFETIME', 30), // 30 days
    ],

];
