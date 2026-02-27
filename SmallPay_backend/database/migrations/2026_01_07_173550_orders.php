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
        Schema::create('orders', function (Blueprint $table) {
            // Clé primaire
            $table->id();

            // Clés étrangères
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // Montants financiers
            $table->decimal('total_amount', 12, 2)->notNullable(); // Prix total avec majoration
            $table->decimal('deposit_amount', 12, 2)->notNullable(); // Acompte versé
            $table->decimal('remaining_amount', 12, 2)->notNullable(); // Montant restant à payer

            // Conditions de paiement
            $table->integer('payment_duration')->notNullable(); // Durée en mois
            $table->decimal('majoration_rate', 5, 2)->notNullable(); // Taux de majoration (%)

            // Statut de la commande
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');

            // Dates importantes
            $table->date('next_due_date')->nullable(); // Prochaine échéance

            // Timestamps
            $table->timestamps();

            // Indexes pour les performances
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('next_due_date');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
