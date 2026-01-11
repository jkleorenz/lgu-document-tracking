<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteAllDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:delete-all {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all document records (keeps users intact)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Confirm before proceeding
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  This will delete ALL documents, status logs, and document-related notifications. Users will be preserved. Continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            $this->info('🔄 Deleting document records...');

            // Delete in correct order (respecting foreign key constraints)
            $statusLogsDeleted = DB::table('document_status_logs')->delete();
            $this->info("  ✓ Deleted {$statusLogsDeleted} status log entries");

            $notificationsDeleted = DB::table('notifications')->whereNotNull('document_id')->delete();
            $this->info("  ✓ Deleted {$notificationsDeleted} notification entries");

            $documentsDeleted = DB::table('documents')->delete();
            $this->info("  ✓ Deleted {$documentsDeleted} documents");

            // Reset auto-increment counters
            DB::statement('ALTER TABLE documents AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE document_status_logs AUTO_INCREMENT = 1');

            $this->info("\n✅ All document records deleted successfully!");
            $this->info("   Users remain intact.");

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error deleting documents: ' . $e->getMessage());
            return 1;
        }
    }
}
