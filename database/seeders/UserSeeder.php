<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates default administrator and sample users
     */
    public function run(): void
    {
        // Get departments
        $mayorOffice = Department::where('code', 'MAYOR')->first();
        $treasDept = Department::where('code', 'MTO')->first(); // MTO (Office of the Municipal Treasurer)
        $budgetDept = Department::where('code', 'BUDGET')->first();
        $engrDept = Department::where('code', 'MEO')->first(); // MEO (Office of the Municipal Engineer)

        // Create Administrator
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@lgu.gov',
            'password' => Hash::make('password'),
            'phone' => '+63 912 345 6789',
            'department_id' => $mayorOffice->id,
            'status' => 'verified',
        ]);
        $admin->assignRole('Administrator');

        $this->command->info('Administrator created:');
        $this->command->info('  Email: admin@lgu.gov');
        $this->command->info('  Password: password');

        // Create Department Head for Treasurer's Office
        $treasHead = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria.santos@lgu.gov',
            'password' => Hash::make('password'),
            'phone' => '+63 912 345 6780',
            'department_id' => $treasDept->id,
            'status' => 'verified',
        ]);
        $treasHead->assignRole('Department Head');

        // Update Treasurer's Office department head
        $treasDept->update(['head_id' => $treasHead->id]);

        // Create Department Head for Budget Office
        $budgetHead = User::create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan.delacruz@lgu.gov',
            'password' => Hash::make('password'),
            'phone' => '+63 912 345 6781',
            'department_id' => $budgetDept->id,
            'status' => 'verified',
        ]);
        $budgetHead->assignRole('Department Head');

        // Update Budget Office department head
        $budgetDept->update(['head_id' => $budgetHead->id]);

        // Create LGU Staff
        $staff1 = User::create([
            'name' => 'Ana Reyes',
            'email' => 'ana.reyes@lgu.gov',
            'password' => Hash::make('password'),
            'phone' => '+63 912 345 6782',
            'department_id' => $treasDept->id,
            'status' => 'verified',
        ]);
        $staff1->assignRole('LGU Staff');

        $staff2 = User::create([
            'name' => 'Pedro Garcia',
            'email' => 'pedro.garcia@lgu.gov',
            'password' => Hash::make('password'),
            'phone' => '+63 912 345 6783',
            'department_id' => $budgetDept->id,
            'status' => 'verified',
        ]);
        $staff2->assignRole('LGU Staff');

        $staff3 = User::create([
            'name' => 'Carmen Lopez',
            'email' => 'carmen.lopez@lgu.gov',
            'password' => Hash::make('password'),
            'phone' => '+63 912 345 6784',
            'department_id' => $engrDept->id,
            'status' => 'verified',
        ]);
        $staff3->assignRole('LGU Staff');

        // Create Pending User (for testing verification)
        $pendingUser = User::create([
            'name' => 'Roberto Mendoza',
            'email' => 'roberto.mendoza@lgu.gov',
            'password' => Hash::make('password'),
            'phone' => '+63 912 345 6785',
            'department_id' => $engrDept->id,
            'status' => 'pending',
        ]);
        $pendingUser->assignRole('LGU Staff');

        $this->command->info('Sample users created successfully!');
        $this->command->info('All sample users have password: password');
    }
}

