<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SMS services including AloohSMS
    |
    */

    'default' => env('SMS_DRIVER', 'alooh'),

    'drivers' => [
        'alooh' => [
            'api_url' => env('ALOOH_SMS_API_URL', 'https://www.aloohsms.com/alooh-sms-gateway/api/sendMessage'),
            // NOUVELLES CLÉS
            'username' => env('ALOOH_SMS_USERNAME'),
            'password' => env('ALOOH_SMS_PASSWORD'),
            'senderName' => env('ALOOH_SMS_SENDER_NAME', 'GODLOVESHOP'),
            'timeout' => 1000,
        ],
        'camoo' => [
            'api_url' => env('CAMOO_SMS_API_URL', 'https://api.camoo.cm/v1/sms/send'),
            'api_key' => env('CAMOO_SMS_API_KEY'),
            'sender' => env('CAMOO_SMS_SENDER', 'GODLOVESHOP'),
            'timeout' => 1000,
        ],
    ],

    'otp' => [
        'length' => 6,
        'expiry_minutes' => 10,
        'max_attempts' => 3,
    ],
];
