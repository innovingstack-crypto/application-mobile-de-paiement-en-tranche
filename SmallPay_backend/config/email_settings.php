<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email Settings Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration centralisée pour le système d'email de SmallPay
    |
    */

    'admin_email' => env('ADMIN_EMAIL', 'contact@godloveshop.cm'),
    'admin_name' => env('ADMIN_NAME', 'SmallPay Admin'),

    /*
    |--------------------------------------------------------------------------
    | Email Templates
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'kyc_submitted' => 'emails.kyc_submitted',
        'first_payment' => 'emails.first_payment',
        'monthly_payment' => 'emails.monthly_payment',
        'payment_due_reminder' => 'emails.payment_due_reminder',
        'payment_overdue' => 'emails.payment_overdue',
        'admin_notification' => 'emails.admin_notification',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Reminders Configuration
    |--------------------------------------------------------------------------
    */

    'reminders' => [
        // Nombre de jours avant l'échéance pour envoyer le rappel
        'days_before_due' => 3,

        // Heure du jour pour envoyer les rappels (format 24h)
        'reminder_time' => '08:00',

        // Fuseau horaire
        'timezone' => 'Africa/Douala',
    ],

    /*
    |--------------------------------------------------------------------------
    | Overdue Notifications Configuration
    |--------------------------------------------------------------------------
    */

    'overdue' => [
        // Heure du jour pour envoyer les notifications de retard
        'notification_time' => '10:00',

        // Fuseau horaire
        'timezone' => 'Africa/Douala',

        // Envoyer un email chaque jour pour les retards
        'send_daily' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => [
        // File d'attente pour les emails
        'connection' => env('QUEUE_CONNECTION', 'database'),

        // Délai (en secondes) avant de traiter les emails
        'delay' => 0,

        // Nombre de tentatives
        'tries' => 3,

        // Délai entre les tentatives (en secondes)
        'retry_after' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Triggers
    |--------------------------------------------------------------------------
    */

    'triggers' => [
        // Envoyer un email quand un KYC est soumis
        'kyc_submitted' => true,

        // Envoyer un email pour le premier versement
        'first_payment' => true,

        // Envoyer un email pour chaque versement mensuel
        'monthly_payment' => true,

        // Envoyer un rappel 3 jours avant l'échéance
        'payment_reminder' => true,

        // Envoyer une notification quand l'échéance est dépassée
        'payment_overdue' => true,

        // Envoyer une notification à l'admin
        'admin_notifications' => true,
    ],
];
