<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    protected $fillable = [
        'employee_name',
        'status',
        'period_month',
        'total_amount',
        'working_days',
        'days_present',
        'days_absent',
        'computed_amount',
        'pdf_path',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'date',
            'total_amount' => 'decimal:2',
            'computed_amount' => 'decimal:2',
            'working_days' => 'integer',
            'days_present' => 'integer',
            'days_absent' => 'integer',
        ];
    }

    /**
     * Payslip status categories (slides 11-13).
     */
    public const STATUSES = [
        'PAYSLIP',
        'READMITTED',
        'TURNBACK',
    ];

    /**
     * Compute the net pay from the total, deducting for days not rendered.
     *
     * Per slide 12: "Total payslip - days of duty" — deduct a per-day amount
     * for each day the employee did not report, from the total salary.
     *
     * daily_rate   = total / working_days
     * computed      = total - (daily_rate * days_absent)
     *
     * Returns the total unchanged when there is no basis to pro-rate.
     */
    public static function computeNet(float $total, ?int $workingDays, ?int $daysAbsent): float
    {
        if (! $workingDays || $workingDays <= 0 || ! $daysAbsent || $daysAbsent <= 0) {
            return round($total, 2);
        }

        $dailyRate = $total / $workingDays;
        $deduction = $dailyRate * min($daysAbsent, $workingDays);

        return round(max($total - $deduction, 0), 2);
    }

    /**
     * The user who uploaded the payslip.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
