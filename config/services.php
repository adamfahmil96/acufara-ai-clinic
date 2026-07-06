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

    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
        'acufara_number' => env('ACUFARA_WHATSAPP_NUMBER'),
        'check_enabled' => env('FONNTE_CHECK_ENABLED', false),
        'check_secret' => env('FONNTE_CHECK_SECRET'),
        'check_interval' => env('FONNTE_CHECK_INTERVAL', 5),
        'email_throttle' => env('FONNTE_EMAIL_THROTTLE', 30),
        'monitoring_email' => env('MONITORING_EMAIL'),
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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model'   => env('GEMINI_DEFAULT_MODEL', 'gemini-3.1-flash-lite'),
    ],

];
