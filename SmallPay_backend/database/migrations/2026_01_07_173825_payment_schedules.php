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
        Schema::create('payment_schedules', function (Blueprint $table) {
            // Clé primaire
            $table->id();

            // Clé étrangère
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // Informations d'échéance
            $table->date('due_date')->notNullable();
            $table->decimal('amount', 12, 2)->notNullable();
            $table->integer('installment_number')->notNullable(); // Numéro de l'échéance (1, 2, 3...)

            // Statut de l'échéance
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');

            // Timestamps
            $table->timestamps();

            // Indexes pour les performances
            $table->index(['order_id', 'installment_number']);
            $table->index(['order_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index('due_date');

            // Contrainte unique : un order ne peut pas avoir deux échéances avec le même numéro
            $table->unique(['order_id', 'installment_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
