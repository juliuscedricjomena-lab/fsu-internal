<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Show dashboard landing page
     */
    public function landing()
    {
        $user = auth()->user();
        
        // Get available modules based on user role
        $modules = $this->getModulesForUser($user);

        return Inertia::render('Dashboard/Landing', [
            'modules' => $modules,
            'user' => $user,
        ]);
    }

    /**
     * Get modules available for the user's role
     */
    private function getModulesForUser($user)
    {
        $modules = [];

        if ($user && $user->role) {
            $roleId = $user->role_id;

            // Define modules by role
            $roleModules = [
                1 => ['Collection', 'Finance', 'Remittance', 'Disbursement', 'Reports', 'Admin'],  // ALL_ACCESS
                2 => ['Collection', 'Finance', 'Remittance', 'Disbursement', 'Reports', 'Admin'],  // ADMINISTRATOR
                3 => ['Collection', 'Finance', 'Remittance', 'Disbursement', 'Reports'],           // CO_ADMIN
                4 => ['Collection', 'Reports'],                                                     // COLLECTION_STAFF
                5 => ['Finance', 'Remittance', 'Reports'],                                          // FINANCE_STAFF
                6 => ['Disbursement', 'Reports'],                                                   // DISBURSEMENT_OFFICER
                7 => ['Reports'],                                                                   // VIEWER
            ];

            $modules = $roleModules[$roleId] ?? [];
        }

        // Return module data with metadata
        return collect($modules)->map(function ($module) {
            return [
                'name' => $module,
                'path' => strtolower($module),
                'description' => $this->getModuleDescription($module),
                'icon' => $this->getModuleIcon($module),
            ];
        })->toArray();
    }

    /**
     * Get module description
     */
    private function getModuleDescription($module)
    {
        $descriptions = [
            'Collection' => 'Manage collection of funds and receivables',
            'Finance' => 'Financial reporting and analysis',
            'Remittance' => 'Upload and manage PAG-IBIG and PHILHEALTH remittances',
            'Disbursement' => 'Manage fund disbursements and payments',
            'Reports' => 'View and generate financial reports',
            'Admin' => 'System administration and settings',
        ];

        return $descriptions[$module] ?? '';
    }

    /**
     * Get module icon
     */
    private function getModuleIcon($module)
    {
        $icons = [
            'Collection' => '💰',
            'Finance' => '📊',
            'Remittance' => '📤',
            'Disbursement' => '💸',
            'Reports' => '📈',
            'Admin' => '⚙️',
        ];

        return $icons[$module] ?? '📦';
    }
}
