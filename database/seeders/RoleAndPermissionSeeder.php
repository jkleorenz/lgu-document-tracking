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
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Administrator role with all permissions
        $adminRole = Role::create(['name' => 'Administrator']);
        $adminRole->givePermissionTo(Permission::all());

        // Create LGU Staff role
        // LGU Staff can ONLY scan QR, update status, forward documents, and view documents
        $staffRole = Role::create(['name' => 'LGU Staff']);
        $staffRole->givePermissionTo([
            'view documents',        // View documents
            'scan qr codes',         // Scan QR code
            'update status',         // Update status via QR scanning
            'receive notifications', // Receive notifications
        ]);

        // Create Department Head role
        // Department Head can scan QR, update status, forward documents, archive, and view department documents
        $deptHeadRole = Role::create(['name' => 'Department Head']);
        $deptHeadRole->givePermissionTo([
            'view documents',            // View forwarded documents
            'view department documents', // View documents in their department
            'archive documents',         // Archive document
            'scan qr codes',             // Scan QR code
            'update status',             // Update status via QR scanning
            'receive notifications',     // Receive notifications
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}

