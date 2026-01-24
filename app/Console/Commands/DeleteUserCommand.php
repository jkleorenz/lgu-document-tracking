<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\LoginAttempt;
use App\Models\Otp;
use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete {email : The email of the user to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a user and all their associated data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Find the user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Confirm deletion
        if (!$this->confirm("Are you sure you want to delete user '{$user->name}' ({$email}) and ALL associated data?", false)) {
            $this->info('Deletion cancelled.');
            return 0;
        }

        try {
            DB::beginTransaction();

            $userId = $user->id;

            // Delete all associated data
            $this->info("Deleting data for user ID: {$userId}...");

            // Delete notifications
            $notificationsCount = Notification::where('user_id', $userId)->count();
            Notification::where('user_id', $userId)->delete();
            $this->info("  - Deleted {$notificationsCount} notifications");

            // Delete audit logs
            $auditLogsCount = AuditLog::where('user_id', $userId)->count();
            AuditLog::where('user_id', $userId)->delete();
            $this->info("  - Deleted {$auditLogsCount} audit logs");

            // Delete login attempts
            $loginAttemptsCount = LoginAttempt::where('email', $email)->count();
            LoginAttempt::where('email', $email)->delete();
            $this->info("  - Deleted {$loginAttemptsCount} login attempts");

            // Delete OTPs
            $otpsCount = Otp::where('email', $email)->count();
            Otp::where('email', $email)->delete();
            $this->info("  - Deleted {$otpsCount} OTPs");

            // Force delete soft-deleted documents created by user
            $documentsCount = Document::where('created_by', $userId)->count();
            Document::where('created_by', $userId)->forceDelete();
            $this->info("  - Deleted {$documentsCount} documents");

            // Delete the user (force delete if soft deletes enabled)
            $user->forceDelete();
            $this->info("  - Deleted user account");

            DB::commit();

            $this->info("\n✓ User '{$user->name}' ({$email}) and all associated data have been successfully deleted.");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error deleting user: {$e->getMessage()}");
            return 1;
        }
    }
}
