<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckUsersCommand extends Command
{
    protected $signature = 'users:check';
    protected $description = 'Check users table for active and soft-deleted records';

    public function handle(): int
    {
        $db = config('database.default');
        $conn = config("database.connections.$db");
        $this->info("Database: {$conn['database']} (connection: {$db})");

        $total = User::withTrashed()->count();
        $active = User::count();
        $deleted = User::onlyTrashed()->count();

        $this->line("Total users (including soft-deleted): {$total}");
        $this->line("Active users: {$active}");
        $this->line("Soft-deleted users: {$deleted}");

        $this->newLine();
        $this->info('Sample records (id, email, name, deleted_at):');
        $rows = User::withTrashed()
            ->select('id', 'email', 'name', 'deleted_at')
            ->orderBy('id')
            ->limit(10)
            ->get();

        if ($rows->isEmpty()) {
            $this->line('No users found.');
        } else {
            $this->table(['id', 'email', 'name', 'deleted_at'], $rows->map(function ($u) {
                return [
                    'id' => $u->id,
                    'email' => $u->email,
                    'name' => $u->name,
                    'deleted_at' => $u->deleted_at,
                ];
            })->toArray());
        }

        return Command::SUCCESS;
    }
}

