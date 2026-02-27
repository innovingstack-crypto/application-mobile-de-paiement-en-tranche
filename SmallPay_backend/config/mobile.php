<?php

return [
    /**
     * Configuration pour l'application mobile React Native
     */
    'api' => [
        'base_url' => env('API_BASE_URL', 'http://localhost:8000/api/mobile'),
        'timeout' => env('API_TIMEOUT', 30),
        'debug' => env('API_DEBUG', true),
    ],

    /**
     * Configuration CORS pour le développement mobile
     */
    'cors' => [
        'allowed_origins' => [
            env('MOBILE_APP_URL', 'http://localhost:19006'),
            'http://localhost:19006',
            'http://localhost:19002',
            'http://127.0.0.1:19006',
            'http://127.0.0.1:19002',
            'exp://127.0.0.1:19000',
            'exp://localhost:19000',
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept'],
        'max_age' => 86400, // 24 heures
    ],

    /**
     * Configuration des endpoints mobiles
     */
    'endpoints' => [
        'register' => '/register',
        'login' => '/login',
        'verify_otp' => '/verify-otp',
        'profile' => '/profile',
        'logout' => '/logout',
        'refresh' => '/refresh',
        'check_availability' => '/check-availability',
        'otp_status' => '/otp-status',
        'request_password_reset' => '/request-password-reset',
        'reset_password' => '/reset-password',
    ],

    /**
     * Configuration des réponses API pour mobile
     */
    'responses' => [
        'success' => true,
        'error_codes' => [
            'REGISTRATION_FAILED' => 'Échec de l\'inscription',
            'OTP_VERIFICATION_FAILED' => 'Échec de la vérification OTP',
            'OTP_REQUEST_FAILED' => 'Échec de la demande OTP',
            'AUTHENTICATION_FAILED' => 'Échec de l\'authentification',
            'VALIDATION_FAILED' => 'Échec de la validation',
            'SERVER_ERROR' => 'Erreur serveur',
        ],
    ],
];