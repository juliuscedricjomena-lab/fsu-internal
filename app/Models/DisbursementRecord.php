<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisbursementRecord extends Model
{
    protected $fillable = [
        'fund_type',
        'expense_group',
        'expense_class',
        'project_particular',
        'payee',
        'reference_no',
        'disbursement_date',
        'amount',
        'mode_of_disbursement',
        'attachment_path',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'disbursement_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Fund types (slide 7: Appropriated vs Non-Appropriated/Trust).
     */
    public const FUND_TYPES = [
        'appropriated' => 'Appropriated Funds',
        'non_appropriated' => 'Non-Appropriated Funds',
    ];

    /**
     * Expense groups keyed by code.
     */
    public const EXPENSE_GROUPS = [
        'PS' => 'Personnel Services',
        'MOOE' => 'MOOE',
        'CO' => 'Capital Outlay',
        'Trust' => 'Trust / Non-Appropriated',
    ];

    /**
     * Expense classes grouped by their parent group (per slide 7).
     * Appropriated funds use PS/MOOE/CO; non-appropriated uses Trust.
     */
    public const EXPENSE_CLASSES = [
        'PS' => [
            'Pay and Allowances (Backpay/Differential)',
            'Subsistence Allowance',
            'Pay and Allowances (Special Financial Assistance)',
            'Reimbursement of Hospitalization Expenses',
            'Replacement Clothing Allowance',
            'NUP Clothing Allowance',
            'Initial Clothing Allowance',
            'Honoraria of Guest Professors',
            'Cadet Clothing Allowance',
            'PhilHealth / Pag-IBIG',
        ],
        'MOOE' => [
            'PNPA',
            'Fixed Expenditures',
        ],
        'CO' => [
            'Capital Outlay',
        ],
        'Trust' => [
            'PNP Trust Receipts Fund',
            'TR Funded Projects',
            'NPC Shares',
            'Election Fund',
            'Trust Liabilities',
        ],
    ];

    /**
     * Common modes of disbursement (slide 8 shows "LDDAP ADA").
     */
    public const MODES_OF_DISBURSEMENT = [
        'LDDAP ADA',
        'Check',
        'Cash',
        'Bank Transfer',
    ];

    /**
     * Which expense groups belong to each fund type.
     */
    public const GROUPS_BY_FUND = [
        'appropriated' => ['PS', 'MOOE', 'CO'],
        'non_appropriated' => ['Trust'],
    ];

    /**
     * The user who recorded the disbursement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
