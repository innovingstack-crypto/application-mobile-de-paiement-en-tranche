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
        // This migration is intentionally defensive to avoid duplicate column errors
        // when running on fresh installs or older schemas.
        $columns = [
            'data' => function (Blueprint $table) {
                $table->json('data')->nullable();
            },
            'client_id_number' => function (Blueprint $table) {
                $table->string('client_id_number')->nullable();
            },
            'client_phone' => function (Blueprint $table) {
                $table->string('client_phone')->nullable();
            },
            'guarantor_name' => function (Blueprint $table) {
                $table->string('guarantor_name')->nullable();
            },
            'guarantor_phone' => function (Blueprint $table) {
                $table->string('guarantor_phone')->nullable();
            },
            'id_front_path' => function (Blueprint $table) {
                $table->string('id_front_path')->nullable();
            },
            'id_back_path' => function (Blueprint $table) {
                $table->string('id_back_path')->nullable();
            },
            'client_photo_path' => function (Blueprint $table) {
                $table->string('client_photo_path')->nullable();
            },
            'signed_document_path' => function (Blueprint $table) {
                $table->string('signed_document_path')->nullable();
            },
            'guarantor_id_front_path' => function (Blueprint $table) {
                $table->string('guarantor_id_front_path')->nullable();
            },
            'guarantor_id_back_path' => function (Blueprint $table) {
                $table->string('guarantor_id_back_path')->nullable();
            },
            'client_verified' => function (Blueprint $table) {
                $table->boolean('client_verified')->default(false);
            },
            'guarantor_verified' => function (Blueprint $table) {
                $table->boolean('guarantor_verified')->default(false);
            },
        ];

        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn('kycs', $column)) {
                Schema::table('kycs', function (Blueprint $table) use ($definition) {
                    $definition($table);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'data',
            'client_id_number',
            'client_phone',
            'guarantor_name',
            'guarantor_phone',
            'id_front_path',
            'id_back_path',
            'client_photo_path',
            'signed_document_path',
            'guarantor_id_front_path',
            'guarantor_id_back_path',
            'client_verified',
            'guarantor_verified',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('kycs', $column)) {
                Schema::table('kycs', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
