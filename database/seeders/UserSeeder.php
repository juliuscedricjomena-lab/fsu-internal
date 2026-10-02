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
        // Map role names to IDs (roles are created by RoleSeeder).
        $roleIds = Role::pluck('id', 'name');

        // FSU PNPA personnel (from the official Access Restriction list).
        $users = [
            [
                'name' => 'PCOL Reyman G Tolentin',
                'email' => 'reyman.tolentin@fsu.local',
                'designation' => 'Chief, FSU PNPA',
                'rank' => 'PCOL',
                'role' => 'ALL_ACCESS',
            ],
            [
                'name' => 'PLTCOL Sheryl O Macarangal',
                'email' => 'sheryl.macarangal@fsu.local',
                'designation' => 'Assistant Chief, FSU PNPA',
                'rank' => 'PLTCOL',
                'role' => 'ADMINISTRATOR',
            ],
            [
                'name' => 'PEMS Carol Joyce A Panganiban',
                'email' => 'carol.panganiban@fsu.local',
                'designation' => 'Chief Clerk / Admin PNCO',
                'rank' => 'PEMS',
                'role' => 'ADMINISTRATOR',
            ],
            [
                'name' => 'PSMS Rufino C Mateo',
                'email' => 'rufino.mateo@fsu.local',
                'designation' => 'PAS / Training PNCO',
                'rank' => 'PSMS',
                'role' => 'CO_ADMIN',
            ],
            [
                'name' => 'PSMS Abigail G Gonzales',
                'email' => 'abigail.gonzales@fsu.local',
                'designation' => 'MDS / Finance PNCO',
                'rank' => 'PSMS',
                'role' => 'ALL_ACCESS',
            ],
            [
                'name' => 'PSMS Alexis E Madarang',
                'email' => 'alexis.madarang@fsu.local',
                'designation' => 'Asst. MDS / Bookkeeper',
                'rank' => 'PSMS',
                'role' => 'CO_ADMIN',
            ],
            [
                'name' => 'PMSg Marlon P Legion',
                'email' => 'marlon.legion@fsu.local',
                'designation' => 'Logistics PNCO',
                'rank' => 'PMSg',
                'role' => 'VIEWER',
            ],
            [
                'name' => 'PMSg Claimar Q Dacumos',
                'email' => 'claimar.dacumos@fsu.local',
                'designation' => 'Asst. Admin PNCO',
                'rank' => 'PMSg',
                'role' => 'CO_ADMIN',
            ],
            [
                'name' => 'PSSg Vien Lionel Pelayo',
                'email' => 'vien.pelayo@fsu.local',
                'designation' => 'Asst. PAS / Supply',
                'rank' => 'PSSg',
                'role' => 'CO_ADMIN',
            ],
            [
                'name' => 'NUP Lyka L Retaga',
                'email' => 'lyka.retaga@fsu.local',
                'designation' => 'Collecting NUP',
                'rank' => 'NUP',
                'role' => 'CO_ADMIN',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'designation' => $userData['designation'],
                    'rank' => $userData['rank'],
                    'status' => 'active',
                    'role_id' => $roleIds[$userData['role']] ?? null,
                ]
            );
        }

        // Keep the users table authoritative: remove anyone not on the official list.
        $officialEmails = array_column($users, 'email');
        User::whereNotIn('email', $officialEmails)->delete();
    }
}
