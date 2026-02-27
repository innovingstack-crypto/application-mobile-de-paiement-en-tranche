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
        Schema::table('products', function (Blueprint $table) {
            // Images secondaires - JSON array
            if (!Schema::hasColumn('products', 'secondary_images')) {
                $table->json('secondary_images')->nullable()->after('image_url');
            }
        });

        // Migrer les données : copier image_url dans main_image (optionnel, après)
        // UPDATE products SET main_image = image_url WHERE image_url IS NOT NULL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'secondary_images')) {
                $table->dropColumn('secondary_images');
            }
        });
    }
};
