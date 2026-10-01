<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            // Collection permissions
            ['name' => 'create_collection', 'description' => 'Create collection transaction'],
            ['name' => 'view_collection', 'description' => 'View collection transactions'],
            ['name' => 'edit_collection', 'description' => 'Edit collection transactions'],
            ['name' => 'delete_collection', 'description' => 'Delete collection transactions'],

            // Finance permissions
            ['name' => 'create_finance', 'description' => 'Create financial records'],
            ['name' => 'view_finance', 'description' => 'View financial records'],
            ['name' => 'edit_finance', 'description' => 'Edit financial records'],
            ['name' => 'delete_finance', 'description' => 'Delete financial records'],

            // Disbursement permissions
            ['name' => 'create_disbursement', 'description' => 'Create disbursement'],
            ['name' => 'view_disbursement', 'description' => 'View disbursement'],
            ['name' => 'approve_disbursement', 'description' => 'Approve disbursement'],
            ['name' => 'delete_disbursement', 'description' => 'Delete disbursement'],

            // Report permissions
            ['name' => 'view_reports', 'description' => 'View reports'],
            ['name' => 'generate_reports', 'description' => 'Generate reports'],
            ['name' => 'export_reports', 'description' => 'Export reports'],

            // Admin permissions
            ['name' => 'manage_users', 'description' => 'Manage system users'],
            ['name' => 'manage_roles', 'description' => 'Manage roles and permissions'],
            ['name' => 'manage_settings', 'description' => 'Manage system settings'],
            ['name' => 'view_audit_log', 'description' => 'View audit logs'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }

        // Attach permissions to roles
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(): void
    {
        $allAccessRole = Role::where('name', 'ALL_ACCESS')->first();
        $adminRole = Role::where('name', 'ADMINISTRATOR')->first();
        $coAdminRole = Role::where('name', 'CO_ADMIN')->first();
        $collectionRole = Role::where('name', 'COLLECTION_STAFF')->first();
        $financeRole = Role::where('name', 'FINANCE_STAFF')->first();
        $disbursementRole = Role::where('name', 'DISBURSEMENT_OFFICER')->first();
        $viewerRole = Role::where('name', 'VIEWER')->first();

        // ALL_ACCESS: All permissions
        if ($allAccessRole) {
            $allAccessRole->permissions()->sync(Permission::pluck('id'));
        }

        // ADMINISTRATOR: All permissions except audit
        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::pluck('id'));
        }

        // CO_ADMIN: Most permissions
        if ($coAdminRole) {
            $coAdminPermissions = Permission::whereIn('name', [
                'create_collection', 'view_collection', 'edit_collection',
                'create_finance', 'view_finance', 'edit_finance',
                'create_disbursement', 'view_disbursement', 'approve_disbursement',
                'view_reports', 'generate_reports',
                'manage_users', 'manage_roles',
            ])->pluck('id');
            $coAdminRole->permissions()->sync($coAdminPermissions);
        }

        // COLLECTION_STAFF: Collection only
        if ($collectionRole) {
            $collectionPermissions = Permission::whereIn('name', [
                'create_collection', 'view_collection', 'edit_collection',
                'view_reports',
            ])->pluck('id');
            $collectionRole->permissions()->sync($collectionPermissions);
        }

        // FINANCE_STAFF: Finance only
        if ($financeRole) {
            $financePermissions = Permission::whereIn('name', [
                'create_finance', 'view_finance', 'edit_finance',
                'view_reports', 'generate_reports',
            ])->pluck('id');
            $financeRole->permissions()->sync($financePermissions);
        }

        // DISBURSEMENT_OFFICER: Disbursement only
        if ($disbursementRole) {
            $disbursementPermissions = Permission::whereIn('name', [
                'view_disbursement', 'approve_disbursement',
                'view_reports',
            ])->pluck('id');
            $disbursementRole->permissions()->sync($disbursementPermissions);
        }

        // VIEWER: View only
        if ($viewerRole) {
            $viewerPermissions = Permission::whereIn('name', [
                'view_collection', 'view_finance', 'view_disbursement', 'view_reports',
            ])->pluck('id');
            $viewerRole->permissions()->sync($viewerPermissions);
        }
    }
}
