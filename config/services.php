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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'public_key' => env('PAYMONGO_PUBLIC_KEY'),
        'webhook_token' => env('PAYMONGO_WEBHOOK_TOKEN'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => (empty(env('GOOGLE_REDIRECT_URI')) || env('GOOGLE_REDIRECT_URI') === 'null')
            ? rtrim((string) env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/auth/google/callback'
            : str_replace('${APP_URL}', rtrim((string) env('APP_URL', 'http://127.0.0.1:8000'), '/'), (string) env('GOOGLE_REDIRECT_URI')),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => (empty(env('FACEBOOK_REDIRECT_URI')) || env('FACEBOOK_REDIRECT_URI') === 'null')
            ? rtrim((string) env('APP_URL', 'http://127.0.0.1:8000'), '/') . '/auth/facebook/callback'
            : str_replace('${APP_URL}', rtrim((string) env('APP_URL', 'http://127.0.0.1:8000'), '/'), (string) env('FACEBOOK_REDIRECT_URI')),
    ],
];
