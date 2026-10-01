<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'ADMINISTRATOR')->first() ?? Role::create(['name' => 'ADMINISTRATOR', 'description' => 'System administrator']);
        $collectionRole = Role::where('name', 'COLLECTION_STAFF')->first() ?? Role::create(['name' => 'COLLECTION_STAFF', 'description' => 'Collection staff member']);
        $financeRole = Role::where('name', 'FINANCE_STAFF')->first() ?? Role::create(['name' => 'FINANCE_STAFF', 'description' => 'Finance staff member']);
        $viewerRole = Role::where('name', 'VIEWER')->first() ?? Role::create(['name' => 'VIEWER', 'description' => 'View-only access']);

        // Create test users
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@fsu.local',
                'password' => Hash::make('password123'),
                'designation' => 'System Administrator',
                'rank' => 'Executive',
                'status' => 'active',
                'role_id' => $adminRole->id,
            ],
            [
                'name' => 'John Collector',
                'email' => 'collector@fsu.local',
                'password' => Hash::make('password123'),
                'designation' => 'Collection Officer',
                'rank' => 'Officer',
                'status' => 'active',
                'role_id' => $collectionRole->id,
            ],
            [
                'name' => 'Jane Finance',
                'email' => 'finance@fsu.local',
                'password' => Hash::make('password123'),
                'designation' => 'Finance Officer',
                'rank' => 'Officer',
                'status' => 'active',
                'role_id' => $financeRole->id,
            ],
            [
                'name' => 'View Only',
                'email' => 'viewer@fsu.local',
                'password' => Hash::make('password123'),
                'designation' => 'Report Viewer',
                'rank' => 'Staff',
                'status' => 'active',
                'role_id' => $viewerRole->id,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
