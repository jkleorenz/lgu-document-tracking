<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates official LGU departments and removes duplicates
     */
    public function run(): void
    {
        // Official list of 23 departments (arranged alphabetically by name)
        $departments = [
            [
                'name' => 'BAC (Bids and Awards Committee)',
                'code' => 'BAC',
                'description' => 'Handles procurement, bidding, and awards processes',
                'is_active' => true,
            ],
            [
                'name' => 'BOMWASA',
                'code' => 'BOMWASA',
                'description' => 'Municipal Water and Sanitation Administration',
                'is_active' => true,
            ],
            [
                'name' => 'DILG',
                'code' => 'DILG',
                'description' => 'Department of the Interior and Local Government',
                'is_active' => true,
            ],
            [
                'name' => 'GSO - Supply Office',
                'code' => 'GSO',
                'description' => 'General Services Office - Manages supplies, equipment, and property',
                'is_active' => true,
            ],
            [
                'name' => 'HRMO (Human Resource Management Office)',
                'code' => 'HRMO',
                'description' => 'Manages human resources, recruitment, and employee relations',
                'is_active' => true,
            ],
            [
                'name' => 'IAS (Internal Audit Service)',
                'code' => 'IAS',
                'description' => 'Conducts internal audits and ensures compliance',
                'is_active' => true,
            ],
            [
                'name' => 'MAGSO (Municipal Agricultural Services Office)',
                'code' => 'MAGSO',
                'description' => 'Provides agricultural services and support to farmers',
                'is_active' => true,
            ],
            [
                'name' => 'MASSO (Office of the Municipal Assessor)',
                'code' => 'MASSO',
                'description' => 'Assesses real property values and administers property taxes',
                'is_active' => true,
            ],
            [
                'name' => 'MCR (Office of the Municipal Civil Registrar)',
                'code' => 'MCR',
                'description' => 'Handles civil registration and vital statistics',
                'is_active' => true,
            ],
            [
                'name' => 'MDRRMO (Municipal Disaster Risk Reduction and Management Office)',
                'code' => 'MDRRMO',
                'description' => 'Manages disaster risk reduction, preparedness, and response',
                'is_active' => true,
            ],
            [
                'name' => 'MENRO (Municipal Environment and Natural Resources Office)',
                'code' => 'MENRO',
                'description' => 'Manages environmental protection and natural resources',
                'is_active' => true,
            ],
            [
                'name' => 'MEO (Office of the Municipal Engineer)',
                'code' => 'MEO',
                'description' => 'Oversees infrastructure, engineering projects, and public works',
                'is_active' => true,
            ],
            [
                'name' => 'MOTORPOOL',
                'code' => 'MOTORPOOL',
                'description' => 'Manages municipal vehicles and transportation services',
                'is_active' => true,
            ],
            [
                'name' => 'MPDC (Office of the Municipal Planning and Development Coordinator)',
                'code' => 'MPDC',
                'description' => 'Coordinates municipal planning and development programs',
                'is_active' => true,
            ],
            [
                'name' => 'MSWDO (Municipal Social Welfare and Development Office)',
                'code' => 'MSWDO',
                'description' => 'Provides social welfare services and development programs',
                'is_active' => true,
            ],
            [
                'name' => 'MTO (Office of the Municipal Treasurer)',
                'code' => 'MTO',
                'description' => 'Manages revenue collection and treasury operations',
                'is_active' => true,
            ],
            [
                'name' => 'Office of the Municipal Accountant',
                'code' => 'ACCTG',
                'description' => 'Manages accounting records and financial reports',
                'is_active' => true,
            ],
            [
                'name' => 'Office of the Municipal Budget Officer',
                'code' => 'BUDGET',
                'description' => 'Handles budget planning and financial management',
                'is_active' => true,
            ],
            [
                'name' => "Office of the Municipal Mayor",
                'code' => 'MAYOR',
                'description' => 'Main executive office of the Local Government Unit - System Administrator',
                'is_active' => true,
            ],
            [
                'name' => 'OSCA (Offices)',
                'code' => 'OSCA',
                'description' => 'Office of the Senior Citizens Affairs',
                'is_active' => true,
            ],
            [
                'name' => 'RHU (Rural Health Unit)',
                'code' => 'RHU',
                'description' => 'Provides primary healthcare services to the community',
                'is_active' => true,
            ],
            [
                'name' => 'SB (Office of the Sangguniang Bayan)',
                'code' => 'SB',
                'description' => 'Legislative body of the municipality',
                'is_active' => true,
            ],
            [
                'name' => 'Tourism Office',
                'code' => 'TOURISM',
                'description' => 'Promotes tourism and manages tourism-related activities',
                'is_active' => true,
            ],
        ];

        // Get list of official department codes
        $officialCodes = array_column($departments, 'code');

        // Deactivate or delete departments that are NOT in the official list
        $departmentsToRemove = Department::whereNotIn('code', $officialCodes)->get();
        $removedCount = 0;
        
        foreach ($departmentsToRemove as $dept) {
            // Check if department has users or documents before deleting
            $hasUsers = $dept->users()->count() > 0;
            $hasDocuments = $dept->documents()->count() > 0;
            
            if ($hasUsers || $hasDocuments) {
                // Deactivate instead of delete if it has related data
                $dept->update(['is_active' => false]);
                $this->command->warn("Deactivated department: {$dept->code} - {$dept->name} (has related data)");
            } else {
                // Safe to delete if no related data
                $dept->delete();
                $this->command->info("Deleted department: {$dept->code} - {$dept->name}");
            }
            $removedCount++;
        }

        // Create or update official departments
        foreach ($departments as $department) {
            Department::updateOrCreate(
                ['code' => $department['code']],
                $department
            );
        }

        // Verify we have exactly 23 active departments
        $activeCount = Department::where('is_active', true)->count();
        
        $this->command->info("Removed {$removedCount} duplicate/old department(s).");
        $this->command->info("All 23 official departments created/updated successfully!");
        $this->command->info("Total active departments: {$activeCount}");
        $this->command->info('Mayor\'s Office (MAYOR) remains as the administrator department.');
        
        if ($activeCount !== 23) {
            $this->command->error("Warning: Expected 23 departments but found {$activeCount}. Please check for duplicates.");
        }
    }
}

