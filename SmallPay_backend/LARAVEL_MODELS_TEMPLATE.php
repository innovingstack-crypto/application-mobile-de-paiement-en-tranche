<?php

/**
 * TEMPLATES DE MODÈLES LARAVEL POUR LES NOTIFICATIONS PUSH
 * 
 * À placer dans: app/Models/
 * 
 * Contient:
 * 1. NotificationToken.php
 * 2. NotificationPreference.php
 * 3. NotificationLog.php
 * 4. Relation User (à ajouter au modèle User existant)
 */

// ============================================================================
// FILE 1: app/Models/NotificationToken.php
// ============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationToken extends Model
{
    protected $fillable = [
        'user_id',
        'push_token',
        'device_type',
        'enabled',
        'last_used_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation: Un token appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    
    /**
     * Récupérer seulement les tokens activés
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Récupérer les tokens pour Android
     */
    public function scopeAndroid($query)
    {
        return $query->where('device_type', 'android');
    }

    /**
     * Récupérer les tokens pour iOS
     */
    public function scopeIos($query)
    {
        return $query->where('device_type', 'ios');
    }

    /**
     * Récupérer les tokens récents (utilisés dans les 30 jours)
     */
    public function scopeRecent($query)
    {
        return $query->where('last_used_at', '>=', now()->subDays(30))
            ->orWhere('created_at', '>=', now()->subDays(30));
    }

    /**
     * Marquer le token comme utilisé
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Désactiver le token
     */
    public function disable(): void
    {
        $this->update(['enabled' => false]);
    }

    /**
     * Activer le token
     */
    public function enable(): void
    {
        $this->update(['enabled' => true]);
    }
}

// ============================================================================
// FILE 2: app/Models/NotificationPreference.php
// ============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'push_notifications_enabled',
        'email_notifications_enabled',
        'notify_payments',
        'notify_kyc',
        'notify_orders',
        'notify_reminders',
        'notify_promotional',
    ];

    protected $casts = [
        'push_notifications_enabled' => 'boolean',
        'email_notifications_enabled' => 'boolean',
        'notify_payments' => 'boolean',
        'notify_kyc' => 'boolean',
        'notify_orders' => 'boolean',
        'notify_reminders' => 'boolean',
        'notify_promotional' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation: Une préférence appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifier si les notifications push sont activées
     */
    public function arePushNotificationsEnabled(): bool
    {
        return $this->push_notifications_enabled;
    }

    /**
     * Vérifier si les notifications email sont activées
     */
    public function areEmailNotificationsEnabled(): bool
    {
        return $this->email_notifications_enabled;
    }

    /**
     * Vérifier si un type de notification est activé
     */
    public function isTypeEnabled(string $type): bool
    {
        $types = [
            'payments' => 'notify_payments',
            'kyc' => 'notify_kyc',
            'orders' => 'notify_orders',
            'reminders' => 'notify_reminders',
            'promotional' => 'notify_promotional',
        ];

        $column = $types[$type] ?? null;
        if (!$column) {
            return false;
        }

        // Vérifier aussi que les notifications globales sont activées
        return $this->push_notifications_enabled && $this->$column;
    }

    /**
     * Activer tous les types de notifications
     */
    public function enableAll(): void
    {
        $this->update([
            'push_notifications_enabled' => true,
            'email_notifications_enabled' => true,
            'notify_payments' => true,
            'notify_kyc' => true,
            'notify_orders' => true,
            'notify_reminders' => true,
            'notify_promotional' => true,
        ]);
    }

    /**
     * Désactiver tous les types de notifications
     */
    public function disableAll(): void
    {
        $this->update([
            'push_notifications_enabled' => false,
            'email_notifications_enabled' => false,
            'notify_payments' => false,
            'notify_kyc' => false,
            'notify_orders' => false,
            'notify_reminders' => false,
            'notify_promotional' => false,
        ]);
    }
}

// ============================================================================
// FILE 3: app/Models/NotificationLog.php
// ============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'status',
        'push_token',
        'error_message',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation: Un log appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /**
     * Scopes
     */

    /**
     * Récupérer les notifications envoyées avec succès
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Récupérer les notifications livrées
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Récupérer les notifications échouées
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Récupérer les notifications en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Récupérer par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Récupérer de la journée
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Marquer comme envoyée
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Marquer comme livrée
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Marquer comme échouée
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'sent_at' => now(),
            'error_message' => $error,
        ]);
    }
}

// ============================================================================
// À AJOUTER AU MODÈLE USER: app/Models/User.php
// ============================================================================

/**
 * Dans la classe User, ajouter les relations suivantes:
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    // ... code existant ...

    /**
     * Relation: Un utilisateur peut avoir plusieurs push tokens
     */
    public function notificationTokens(): HasMany
    {
        return $this->hasMany(NotificationToken::class);
    }

    /**
     * Relation: Un utilisateur a une préférence de notifications
     */
    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    /**
     * Relation: Un utilisateur peut avoir plusieurs logs de notifications
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Obtenir ou créer la préférence de notifications
     */
    public function getNotificationPreference(): NotificationPreference
    {
        return $this->notificationPreference 
            ?? NotificationPreference::create(['user_id' => $this->id]);
    }

    /**
     * Récupérer tous les tokens actifs de l'utilisateur
     */
    public function getActiveNotificationTokens()
    {
        return $this->notificationTokens()
            ->enabled()
            ->get();
    }

    /**
     * Envoyer une notification push à l'utilisateur
     * 
     * @param string $title Titre de la notification
     * @param string $body Corps de la notification
     * @param string $type Type de notification
     * @param array $data Données additionnelles
     * @param string $channel Channel pour Android
     * @return bool Si l'envoi a réussi
     */
    public function sendPushNotification(
        string $title,
        string $body,
        string $type,
        array $data = [],
        string $channel = 'default'
    ): bool {
        // Vérifier si l'utilisateur a activé les notifications
        $preference = $this->getNotificationPreference();
        
        if (!$preference->push_notifications_enabled) {
            return false;
        }

        // Vérifier si ce type de notification est activé
        if (!$preference->isTypeEnabled($type)) {
            return false;
        }

        // Récupérer les tokens actifs
        $tokens = $this->getActiveNotificationTokens();
        
        if ($tokens->isEmpty()) {
            return false;
        }

        // Envoyer via le notification service
        // À implémenter selon votre service d'envoi
        // Par exemple: Notification::queue(new PushNotification(...))

        return true;
    }

    // ... reste du code ...
}
