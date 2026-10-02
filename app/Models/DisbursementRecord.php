<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisbursementRecord extends Model
{
    protected $fillable = [
        'category',
        'payee',
        'purpose',
        'reference_no',
        'disbursement_date',
        'amount',
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
     * Disbursement categories (per Slide 6).
     */
    public const CATEGORIES = [
        'Personnel Services',
        'MOOE',
        'Capital Outlay',
        'Trust Receipt',
        'Modified Disbursement System',
    ];

    /**
     * The user who recorded the disbursement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
