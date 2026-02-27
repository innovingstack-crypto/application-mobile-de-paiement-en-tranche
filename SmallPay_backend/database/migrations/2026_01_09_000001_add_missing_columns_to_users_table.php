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
        Schema::table('users', function (Blueprint $table) {
            // Ajouter verification_method s'il n'existe pas
            if (!Schema::hasColumn('users', 'verification_method')) {
                $table->enum('verification_method', ['email', 'sms'])->nullable()->after('status');
            }

            // Ajouter is_verified s'il n'existe pas
            if (!Schema::hasColumn('users', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('verification_method');
            }

            // Ajouter verified_at s'il n'existe pas
            if (!Schema::hasColumn('users', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('is_verified');
            }

            // Ajouter reset_token s'il n'existe pas
            if (!Schema::hasColumn('users', 'reset_token')) {
                $table->string('reset_token')->nullable()->unique()->after('verified_at');
            }

            // Ajouter reset_token_expires_at s'il n'existe pas
            if (!Schema::hasColumn('users', 'reset_token_expires_at')) {
                $table->timestamp('reset_token_expires_at')->nullable()->after('reset_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists('verification_method');
            $table->dropColumnIfExists('is_verified');
            $table->dropColumnIfExists('verified_at');
            $table->dropColumnIfExists('reset_token');
            $table->dropColumnIfExists('reset_token_expires_at');
        });
    }
};
