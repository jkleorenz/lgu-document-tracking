<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates sample LGU departments
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => "Mayor's Office",
                'code' => 'MAYOR',
                'description' => 'Main executive office of the Local Government Unit',
                'is_active' => true,
            ],
            [
                'name' => "Treasurer's Office",
                'code' => 'TREAS',
                'description' => 'Manages revenue collection and treasury operations',
                'is_active' => true,
            ],
            [
                'name' => 'Budget Office',
                'code' => 'BUDGET',
                'description' => 'Handles budget planning and financial management',
                'is_active' => true,
            ],
            [
                'name' => 'Engineering Office',
                'code' => 'ENGR',
                'description' => 'Oversees infrastructure and engineering projects',
                'is_active' => true,
            ],
            [
                'name' => 'Planning and Development Office (MPDO)',
                'code' => 'MPDO',
                'description' => 'Municipal planning and development coordination',
                'is_active' => true,
            ],
            [
                'name' => 'Accounting Office',
                'code' => 'ACCTG',
                'description' => 'Manages accounting records and financial reports',
                'is_active' => true,
            ],
            [
                'name' => "Civil Registrar's Office",
                'code' => 'CIVIL',
                'description' => 'Handles civil registration and vital statistics',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        $this->command->info('Departments created successfully!');
    }
}

