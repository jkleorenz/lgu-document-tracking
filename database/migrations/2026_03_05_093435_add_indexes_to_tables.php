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
        Schema::table('documents', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('current_handler_id');
            $table->index('department_id');
            $table->index('status');
            $table->index('document_type');
            $table->index('is_priority');
            $table->index('archived_at');
            $table->index(['status', 'department_id']); // composite for queries
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('department_id');
            $table->index('status');
        });

        Schema::table('document_status_logs', function (Blueprint $table) {
            $table->index('document_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['current_handler_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['document_type']);
            $table->dropIndex(['is_priority']);
            $table->dropIndex(['archived_at']);
            $table->dropIndex(['status', 'department_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['email']);
        });

        Schema::table('document_status_logs', function (Blueprint $table) {
            $table->dropIndex(['document_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
