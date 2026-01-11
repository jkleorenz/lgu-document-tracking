<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'Return' status to documents enum
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `documents` MODIFY COLUMN `status` ENUM('Pending', 'Received', 'Under Review', 'Forwarded', 'Approved', 'Rejected', 'Return', 'Completed', 'Archived') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `documents` MODIFY COLUMN `status` ENUM('Pending', 'Received', 'Under Review', 'Forwarded', 'Approved', 'Rejected', 'Completed', 'Archived') NOT NULL DEFAULT 'Pending'");
    }
};
