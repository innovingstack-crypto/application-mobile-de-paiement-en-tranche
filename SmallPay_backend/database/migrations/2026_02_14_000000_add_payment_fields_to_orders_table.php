<?php

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
        Schema::table('orders', function (Blueprint $table) {
            // Statut du paiement: pending, partial, completed
            $table->enum('payment_status', ['pending', 'partial', 'completed'])->default('pending')->after('status');
            
            // Type de paiement (méthode)
            $table->string('payment_method')->nullable()->after('payment_status');
            
            // Référence Campay pour le paiement de dépôt
            $table->string('deposit_reference')->nullable()->unique()->after('payment_method');
            
            // Date du paiement du dépôt
            $table->timestamp('deposit_paid_at')->nullable()->after('deposit_reference');
            
            // Intérêt total sur la durée de paiement
            $table->decimal('total_interest', 10, 2)->default(0)->after('deposit_paid_at');
            
            // Flag pour vérifier si KYC est requise
            $table->boolean('is_kyc_required')->default(true)->after('total_interest');
            
            // KYC approuvé à la date/heure
            $table->timestamp('kyc_verified_at')->nullable()->after('is_kyc_required');
            
            // ID de l'utilisateur qui a approuvé (admin)
            $table->unsignedBigInteger('verified_by')->nullable()->after('kyc_verified_at');
            
            // Index pour les recherches rapides
            $table->index(['user_id', 'payment_status']);
            $table->index(['deposit_reference']);
            $table->index(['payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'payment_status']);
            $table->dropIndex(['deposit_reference']);
            $table->dropIndex(['payment_status']);
            
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'deposit_reference',
                'deposit_paid_at',
                'total_interest',
                'is_kyc_required',
                'kyc_verified_at',
                'verified_by',
            ]);
        });
    }
};
