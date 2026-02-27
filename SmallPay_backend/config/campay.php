<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Campay API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'intégration du système de paiement Campay
    | Utilisé pour les paiements d'acompte et mensuel dans SmallPay
    |
    */

    'api_key' => env('CAMPAY_API_KEY'),

    'username' => env('CAMPAY_USERNAME'),

    'password' => env('CAMPAY_PASSWORD'),

    'webhook_secret' => env('CAMPAY_WEBHOOK_SECRET', ''),

    'currency' => env('CAMPAY_CURRENCY', 'XAF'),

    'base_url' => env('CAMPAY_BASE_URL', 'https://api.campay.net/v1'),

    /*
    |--------------------------------------------------------------------------
    | SmallPay Payment Configuration
    |--------------------------------------------------------------------------
    */

    'payment' => [
        // Pourcentage d'acompte (30%)
        'deposit_percentage' => env('PAYMENT_DEPOSIT_PERCENTAGE', 30),

        // Durée par défaut en mois (6 mois)
        'default_duration' => env('PAYMENT_DEFAULT_DURATION', 6),

        // Taux d'intérêt (0% par défaut - à configurer)
        'interest_rate' => env('PAYMENT_INTEREST_RATE', 0),

        // Montant minimum pour un paiement
        'min_amount' => env('PAYMENT_MIN_AMOUNT', 100),

        // Montant maximum pour un paiement
        'max_amount' => env('PAYMENT_MAX_AMOUNT', 10000000),

        // Max tentatives de paiement par jour
        'max_attempts_per_day' => env('PAYMENT_MAX_ATTEMPTS_PER_DAY', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */

    'webhook' => [
        // URL du webhook pour Campay
        'url' => env('CAMPAY_WEBHOOK_URL', env('APP_URL') . '/api/campay/callback'),

        // Vérifier la signature du webhook
        'verify_signature' => env('CAMPAY_VERIFY_WEBHOOK_SIGNATURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */

    'logging' => [
        // Log tous les appels API Campay
        'log_api_calls' => env('CAMPAY_LOG_API_CALLS', true),

        // Log les webhooks reçus
        'log_webhooks' => env('CAMPAY_LOG_WEBHOOKS', true),

        // Level de log (debug, info, warning, error)
        'log_level' => env('CAMPAY_LOG_LEVEL', 'info'),
    ],
];
