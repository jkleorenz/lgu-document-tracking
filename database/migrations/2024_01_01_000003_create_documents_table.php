<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the documents table for tracking physical documents
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('document_type', [
                'Memorandum', 
                'Letter', 
                'Resolution', 
                'Ordinance', 
                'Report', 
                'Request',
                'Other'
            ])->default('Letter');
            $table->string('qr_code_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('current_handler_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->enum('status', [
                'Pending',
                'Received',
                'Under Review',
                'Forwarded',
                'Approved',
                'Rejected',
                'Archived'
            ])->default('Pending');
            $table->boolean('is_priority')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

