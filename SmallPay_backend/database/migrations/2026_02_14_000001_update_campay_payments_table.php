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
        Schema::table('campay_payments', function (Blueprint $table) {
            // Ajouter foreign keys et relations
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->unsignedBigInteger('order_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('payment_schedule_id')->nullable()->after('order_id');
            
            // Type de paiement: deposit (acompte) ou installment (mensuel)
            $table->enum('payment_type', ['deposit', 'installment'])->default('deposit')->after('payment_schedule_id');
            
            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('set null');
            
            $table->foreign('payment_schedule_id')
                ->references('id')
                ->on('payment_schedules')
                ->onDelete('set null');
            
            // Index pour les recherches
            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'status']);
            $table->index(['payment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campay_payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['order_id']);
            $table->dropForeign(['payment_schedule_id']);
            
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['order_id', 'status']);
            $table->dropIndex(['payment_type']);
            
            $table->dropColumn([
                'user_id',
                'order_id',
                'payment_schedule_id',
                'payment_type',
            ]);
        });
    }
};
