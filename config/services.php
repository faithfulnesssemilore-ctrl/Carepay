<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token'    => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'                 => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paystack' => [
        'public'          => env('PAYSTACK_PUBLIC_KEY'),
        'secret'          => env('PAYSTACK_SECRET_KEY'),
        'url'             => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
        'callback_url'    => env('PAYSTACK_CALLBACK_URL'),
        'currency'        => env('PAYSTACK_CURRENCY', 'NGN'),
        'merchant_email'  => env('PAYSTACK_MERCHANT_EMAIL'),
    ],

    'vtpass' => [
        'base_url'   => env('VTPASS_BASE_URL', 'https://sandbox.vtpass.com/api'),
        'api_key'    => env('VTPASS_API_KEY'),
        'public_key' => env('VTPASS_PUBLIC_KEY'),
        'secret_key' => env('VTPASS_SECRET_KEY'),
    ],

];
