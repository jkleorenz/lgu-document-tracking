<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $treasDept = Department::where('code', 'MTO')->first();
        $budgetDept = Department::where('code', 'BUDGET')->first();
        $engrDept = Department::where('code', 'MEO')->first();

        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@lgu.gov',
                'phone' => '+63 912 345 6789',
                'department_id' => $mayorOffice->id,
                'status' => 'verified',
                'role' => 'Administrator',
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@lgu.gov',
                'phone' => '+63 912 345 6780',
                'department_id' => $treasDept->id,
                'status' => 'verified',
                'role' => 'Department Head',
                'setDepartmentHead' => true,
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@lgu.gov',
                'phone' => '+63 912 345 6781',
                'department_id' => $budgetDept->id,
                'status' => 'verified',
                'role' => 'Department Head',
                'setDepartmentHead' => true,
            ],
            [
                'name' => 'Ana Reyes',
                'email' => 'ana.reyes@lgu.gov',
                'phone' => '+63 912 345 6782',
                'department_id' => $treasDept->id,
                'status' => 'verified',
                'role' => 'LGU Staff',
            ],
            [
                'name' => 'Pedro Garcia',
                'email' => 'pedro.garcia@lgu.gov',
                'phone' => '+63 912 345 6783',
                'department_id' => $budgetDept->id,
                'status' => 'verified',
                'role' => 'LGU Staff',
            ],
            [
                'name' => 'Carmen Lopez',
                'email' => 'carmen.lopez@lgu.gov',
                'phone' => '+63 912 345 6784',
                'department_id' => $engrDept->id,
                'status' => 'verified',
                'role' => 'LGU Staff',
            ],
            [
                'name' => 'Roberto Mendoza',
                'email' => 'roberto.mendoza@lgu.gov',
                'phone' => '+63 912 345 6785',
                'department_id' => $engrDept->id,
                'status' => 'pending',
                'role' => 'LGU Staff',
            ],
        ];

        foreach ($users as $userData) {
            $password = Str::random(12);
            $role = $userData['role'];
            $setDepartmentHead = $userData['setDepartmentHead'] ?? false;
            unset($userData['role'], $userData['setDepartmentHead']);

            $userData['password'] = Hash::make($password);
            $user = User::create($userData);
            $user->assignRole($role);

            if ($setDepartmentHead && $user->department_id) {
                Department::where('id', $user->department_id)->update(['head_id' => $user->id]);
            }

            $this->command->info("  {$userData['name']} ({$userData['email']}) — password: {$password}");
        }

        $this->command->info('Sample users created successfully!');
    }
}

