<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change document_type from ENUM to VARCHAR to support custom document types
     */
    public function up(): void
    {
        // Change ENUM to VARCHAR(255) to allow any document type including custom ones
        DB::statement("ALTER TABLE `documents` MODIFY COLUMN `document_type` VARCHAR(255) NOT NULL DEFAULT 'Letter'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM (with original values)
        DB::statement("ALTER TABLE `documents` MODIFY COLUMN `document_type` ENUM('Memorandum', 'Letter', 'Resolution', 'Ordinance', 'Report', 'Request', 'Other') NOT NULL DEFAULT 'Letter'");
    }
};
