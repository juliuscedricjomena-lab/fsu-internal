<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemittanceFile extends Model
{
    protected $fillable = [
        'type',
        'original_name',
        'stored_path',
        'size',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /**
     * Remittance file categories (per Slide 10).
     */
    public const TYPES = [
        'PAG-IBIG',
        'PHILHEALTH',
    ];

    /**
     * The user who uploaded the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
