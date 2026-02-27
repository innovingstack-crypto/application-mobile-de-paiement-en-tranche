<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter les migrations
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // Clé primaire
            $table->id();

            // Informations de base
            $table->string('name', 255)->notNullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->notNullable();
            // Stock et catégorie
            $table->integer('stock')->default(0)->notNullable();
            $table->string('category', 100)->notNullable();
            // Image
            $table->string('image_url', 500)->nullable();
            $table->json('secondary_images')->nullable();
            // Statut
            $table->boolean('is_active')->default(true)->notNullable();
            // Timestamps
            $table->timestamps();
            // Indexes pour les performances
            $table->index('category');
            $table->index('is_active');
            $table->index('created_at');
            $table->fullText(['name', 'description']); // Pour la recherche full-text
        });
    }

    /**
     * Annuler les migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};