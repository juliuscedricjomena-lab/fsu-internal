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
                1 => ['Collection', 'Pay & Allowances', 'Remittance', 'Disbursement', 'Admin'],  // ALL_ACCESS
                2 => ['Collection', 'Pay & Allowances', 'Remittance', 'Disbursement', 'Admin'],  // ADMINISTRATOR
                3 => ['Collection', 'Pay & Allowances', 'Remittance', 'Disbursement'],           // CO_ADMIN
                4 => ['Collection'],                                                             // COLLECTION_STAFF
                5 => ['Pay & Allowances', 'Remittance'],                                         // FINANCE_STAFF
                6 => ['Disbursement'],                                                           // DISBURSEMENT_OFFICER
                7 => [],                                                                         // VIEWER
            ];

            $modules = $roleModules[$roleId] ?? [];
        }

        // Explicit route slugs for modules whose path differs from the name.
        $paths = [
            'Pay & Allowances' => 'pay-allowances',
        ];

        // Return module data with metadata
        return collect($modules)->map(function ($module) use ($paths) {
            return [
                'name' => $module,
                'path' => $paths[$module] ?? strtolower($module),
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
            'Pay & Allowances' => 'Upload payslips and compute pay with days of duty',
            'Remittance' => 'Upload and manage PAG-IBIG and PHILHEALTH remittances',
            'Disbursement' => 'Manage fund disbursements and payments',
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
            'Pay & Allowances' => '💵',
            'Remittance' => '📤',
            'Disbursement' => '💸',
            'Admin' => '⚙️',
        ];

        return $icons[$module] ?? '📦';
    }
}
