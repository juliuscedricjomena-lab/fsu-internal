<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'ALL_ACCESS', 'description' => 'Full system access'],
            ['name' => 'ADMINISTRATOR', 'description' => 'System administrator'],
            ['name' => 'CO_ADMIN', 'description' => 'Co-administrator'],
            ['name' => 'COLLECTION_STAFF', 'description' => 'Collection staff member'],
            ['name' => 'FINANCE_STAFF', 'description' => 'Finance staff member'],
            ['name' => 'DISBURSEMENT_OFFICER', 'description' => 'Disbursement officer'],
            ['name' => 'VIEWER', 'description' => 'View-only access'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }
    }
}
