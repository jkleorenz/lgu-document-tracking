<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates roles and permissions for the document tracking system
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view documents',
            'create documents',
            'edit documents',
            'delete documents',
            'archive documents',
            'set priority',
            'scan qr codes',
            'update status',
            'manage users',
            'verify users',
            'view all documents',
            'view own documents',
            'view department documents',
            'receive notifications',
            'reset user passwords',
            'view user passwords',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create or update Administrator role with all permissions
        $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
        $adminRole->syncPermissions(Permission::all());

        // Create or update LGU Staff role
        // LGU Staff can create documents, scan QR, update status, archive, and view documents
        // NOTE: LGU Staff and Department Head have identical privileges and features
        $staffRole = Role::firstOrCreate(['name' => 'LGU Staff']);
        $staffRole->syncPermissions([
            'view documents',            // View documents
            'view department documents', // View documents in their department
            'create documents',          // Create documents
            'archive documents',         // Archive documents (can archive and restore)
            'scan qr codes',             // Scan QR code
            'update status',             // Update status via QR scanning
            'receive notifications',     // Receive notifications
        ]);

        // Create or update Department Head role
        // NOTE: Department Head has SAME privileges as LGU Staff - identical permissions and features
        $deptHeadRole = Role::firstOrCreate(['name' => 'Department Head']);
        $deptHeadRole->syncPermissions([
            'view documents',            // View documents
            'view department documents', // View documents in their department
            'create documents',          // Create documents
            'archive documents',         // Archive documents (can archive and restore)
            'scan qr codes',             // Scan QR code
            'update status',             // Update status via QR scanning
            'receive notifications',     // Receive notifications
        ]);

        // Create or update Mayor role
        // Mayor can create documents, view all documents, and manage documents
        $mayorRole = Role::firstOrCreate(['name' => 'Mayor']);
        $mayorRole->syncPermissions([
            'view documents',            // View documents
            'view all documents',        // View all documents
            'create documents',          // Create documents
            'edit documents',            // Edit documents
            'archive documents',         // Archive documents
            'scan qr codes',             // Scan QR code
            'update status',             // Update status
            'set priority',              // Set priority
            'receive notifications',     // Receive notifications
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}

