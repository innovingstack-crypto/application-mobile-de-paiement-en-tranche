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
        Schema::create('kycs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // ===== HIERARCHICAL DATA STRUCTURE (JSON) =====
            // Contains the complete nested structure:
            // {
            //   "client": { fullName, phoneNumber, idNumber, address, email, documents: {photo, idFront, idBack} },
            //   "signedDocument": "uri",
            //   "guarantor": { name, phoneNumber, documents: {idFront, idBack} }
            // }
            $table->json('data')->nullable();
            
            // ===== EXTRACTED FIELDS FOR QUERYING =====
            // For faster queries and indexing
            $table->string('client_id_number')->nullable(); // Extracted from data.client.idNumber
            $table->string('client_phone')->nullable();     // Extracted from data.client.phoneNumber
            $table->string('guarantor_name')->nullable();   // Extracted from data.guarantor.name
            $table->string('guarantor_phone')->nullable();  // Extracted from data.guarantor.phoneNumber
            
            // ===== FILE PATHS (for direct access) =====
            $table->string('id_front_path')->nullable();
            $table->string('id_back_path')->nullable();
            $table->string('client_photo_path')->nullable();
            $table->string('signed_document_path')->nullable();
            $table->string('guarantor_id_front_path')->nullable();
            $table->string('guarantor_id_back_path')->nullable();
            
            // ===== STATUS & APPROVAL =====
            $table->enum('status', ['pending', 'approved', 'rejected', 'under_review'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            // ===== VERIFICATION FLAGS =====
            $table->boolean('client_verified')->default(false);
            $table->boolean('guarantor_verified')->default(false);
            
            $table->timestamps();
            
            // ===== INDICES =====
            $table->index('user_id');
            $table->index('status');
            $table->index('client_id_number');
            $table->index('client_phone');
            $table->index('guarantor_phone');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kycs');
    }
};
