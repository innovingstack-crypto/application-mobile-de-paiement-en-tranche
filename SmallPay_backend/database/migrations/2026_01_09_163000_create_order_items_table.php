<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');

            $table->string('sku')->nullable();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity');

            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['product_id']);
        });

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'product_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'product_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
                $table->index('product_id');
            });
        }

        Schema::dropIfExists('order_items');
    }
};
