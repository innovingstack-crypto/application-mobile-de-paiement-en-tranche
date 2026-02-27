<?php

/**
 * MIGRATION TEMPLATE POUR LES NOTIFICATIONS PUSH
 * 
 * À utiliser pour créer la table de stockage des push tokens
 * 
 * UTILISATION:
 * php artisan make:migration create_notification_tokens_table
 * 
 * Puis copier le contenu de la méthode up() ci-dessous
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table pour stocker les push tokens des utilisateurs
        Schema::create('notification_tokens', function (Blueprint $table) {
            $table->id();
            
            // Relation avec l'utilisateur
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Le push token Expo
            $table->string('push_token')
                ->unique()
                ->comment('Token push unique de Expo');
            
            // Type d'appareil
            $table->string('device_type')
                ->default('unknown')
                ->comment('ios ou android');
            
            // Est-ce que les notifications sont activées pour ce token?
            $table->boolean('enabled')
                ->default(true)
                ->comment('Si false, ne pas envoyer de notifications');
            
            // Quand le token a été utilisé pour la dernière fois
            $table->timestamp('last_used_at')
                ->nullable()
                ->comment('Dernière utilisation pour envoyer une notification');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes pour les performances
            $table->index(['user_id', 'enabled']);
            $table->index('push_token');
            $table->index('created_at');
        });

        // Table pour les préférences de notifications de l'utilisateur
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            
            // Relation avec l'utilisateur
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->unique();
            
            // Notifications push activées globalement?
            $table->boolean('push_notifications_enabled')
                ->default(true)
                ->comment('Toutes les notifications push');
            
            // Notifications par email activées?
            $table->boolean('email_notifications_enabled')
                ->default(false)
                ->comment('Toutes les notifications email');
            
            // Types de notifications à recevoir
            $table->boolean('notify_payments')
                ->default(true)
                ->comment('Notifications de paiements (succès, échec, remboursement)');
            
            $table->boolean('notify_kyc')
                ->default(true)
                ->comment('Mises à jour KYC (approbation, rejet, documents)');
            
            $table->boolean('notify_orders')
                ->default(true)
                ->comment('Notifications de commandes (création, statut, livraison)');
            
            $table->boolean('notify_reminders')
                ->default(true)
                ->comment('Rappels (paiements, limite, etc)');
            
            $table->boolean('notify_promotional')
                ->default(false)
                ->comment('Notifications promotionnelles');
            
            // Timestamps
            $table->timestamps();
            
            // Index
            $table->index('user_id');
        });

        // Table pour tracker les notifications envoyées
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            
            // Relation avec l'utilisateur
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade');
            
            // Information de la notification
            $table->string('type')
                ->comment('Type de notification: payment_success, kyc_approved, etc');
            
            $table->string('title')
                ->comment('Titre de la notification');
            
            $table->text('body')
                ->comment('Corps de la notification');
            
            // Données additionnelles
            $table->json('data')
                ->nullable()
                ->comment('Données JSON additionnelles');
            
            // Status d'envoi
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered'])
                ->default('pending')
                ->comment('État de l\'envoi');
            
            // Le token vers lequel elle a été envoyée
            $table->string('push_token')
                ->nullable()
                ->comment('Token auxquels on a essayé d\'envoyer');
            
            // Message d'erreur si échec
            $table->text('error_message')
                ->nullable()
                ->comment('Message d\'erreur si l\'envoi a échoué');
            
            // Timestamps
            $table->timestamp('sent_at')
                ->nullable()
                ->comment('Quand la notification a été envoyée');
            
            $table->timestamp('delivered_at')
                ->nullable()
                ->comment('Quand la notification a été livrée');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('push_token');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_tokens');
    }
};
