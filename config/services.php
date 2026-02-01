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

    /*
    |--------------------------------------------------------------------------
    | LMS Services
    |--------------------------------------------------------------------------
    */

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME', 'GloboKidsBot'),
    ],

    'yokassa' => [
        'shop_id' => env('YOKASSA_SHOP_ID'),
        'secret_key' => env('YOKASSA_SECRET_KEY'),
        'webhook_secret' => env('YOKASSA_WEBHOOK_SECRET'),
    ],

    'cloudpayments' => [
        'public_id' => env('CLOUDPAYMENTS_PUBLIC_ID'),
        'secret' => env('CLOUDPAYMENTS_SECRET'),
    ],

    'tinkoff' => [
        'terminal_key' => env('TINKOFF_TERMINAL_KEY'),
        'secret_key' => env('TINKOFF_SECRET_KEY'),
    ],

    'tilda' => [
        'webhook_secret' => env('TILDA_WEBHOOK_SECRET'),
    ],

];
