<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'apple' => [
        'pass_certificate' => env('APPLE_PASS_CERTIFICATE_PATH'),
        'pass_password' => env('APPLE_PASS_CERTIFICATE_PASSWORD'),
        'pass_type_identifier' => env('APPLE_PASS_TYPE_IDENTIFIER', 'pass.org.bahamasnationaltrust.member'),
        'team_identifier' => env('APPLE_TEAM_IDENTIFIER', 'BNTTEAMID123'),
    ],

    'google' => [
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID'),
        'class_id' => env('GOOGLE_WALLET_CLASS_ID', 'BNTMemberClass'),
        'client_email' => env('GOOGLE_WALLET_CLIENT_EMAIL'),
        'private_key' => env('GOOGLE_WALLET_PRIVATE_KEY'),
    ],

];
