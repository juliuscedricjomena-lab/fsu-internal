<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionTransaction extends Model
{
    protected $fillable = [
        'account_type',
        'payor_name',
        'transaction_date',
        'or_number',
        'or_attachment_path',
        'amount',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Account types available for a collection transaction (per Slide 16).
     */
    public const ACCOUNT_TYPES = [
        'General Fund',
        'Overpayment',
        'Beyond Economic Repair',
        'Firearms Accountability',
        'Trust Receipt',
        'Light',
        'Water',
        'Bidding Documents',
    ];

    /**
     * The user who prepared/recorded the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
