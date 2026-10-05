<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PayAllowancesController extends Controller
{
    /**
     * Landing: display payslips filtered by month, grouped by status
     * (PAYSLIP / READMITTED / TURNBACK) per slides 11-13.
     */
    public function index(Request $request)
    {
        // Default to the current month; filter is a "YYYY-MM" string.
        $month = $request->string('month')->toString() ?: now()->format('Y-m');

        $query = Payslip::with('uploader:id,name');

        if ($month) {
            try {
                $period = Carbon::createFromFormat('Y-m', $month);
                $query->whereYear('period_month', $period->year)
                    ->whereMonth('period_month', $period->month);
            } catch (\Throwable) {
                // Ignore an invalid month filter and show all.
                $month = '';
            }
        }

        $payslips = $query->orderByDesc('period_month')->orderBy('employee_name')->get();

        // Group into the three status buckets for the tabbed display.
        $grouped = collect(Payslip::STATUSES)->mapWithKeys(fn ($s) => [
            $s => $payslips->where('status', $s)->values(),
        ]);

        return Inertia::render('Modules/PayAllowances/Index', [
            'payslips' => $payslips->values(),
            'grouped' => $grouped,
            'statuses' => Payslip::STATUSES,
            'month' => $month,
            'counts' => [
                'total' => $payslips->count(),
                'computedTotal' => (float) $payslips->sum(fn ($p) => $p->computed_amount ?? $p->total_amount),
            ],
        ]);
    }

    /**
     * Show the upload / computation form.
     */
    public function create()
    {
        return Inertia::render('Modules/PayAllowances/Create', [
            'statuses' => Payslip::STATUSES,
        ]);
    }

    /**
     * Store a payslip (PDF) with the days-of-duty computation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(Payslip::STATUSES)],
            'period_month' => ['required', 'date_format:Y-m'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'working_days' => ['nullable', 'integer', 'min:1', 'max:31'],
            'days_present' => ['nullable', 'integer', 'min:0', 'max:31'],
            'days_absent' => ['nullable', 'integer', 'min:0', 'max:31'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $computed = Payslip::computeNet(
            (float) $validated['total_amount'],
            $validated['working_days'] ?? null,
            $validated['days_absent'] ?? null,
        );

        $path = $request->file('pdf')->store('payslips', 'local');

        Payslip::create([
            'employee_name' => $validated['employee_name'],
            'status' => $validated['status'],
            'period_month' => Carbon::createFromFormat('Y-m', $validated['period_month'])->startOfMonth(),
            'total_amount' => $validated['total_amount'],
            'working_days' => $validated['working_days'] ?? null,
            'days_present' => $validated['days_present'] ?? null,
            'days_absent' => $validated['days_absent'] ?? null,
            'computed_amount' => $computed,
            'pdf_path' => $path,
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('pay-allowances.index')
            ->with('success', 'Payslip uploaded successfully.');
    }

    /**
     * Stream the payslip PDF inline for in-browser preview.
     */
    public function preview(Payslip $payslip)
    {
        abort_if(! $payslip->pdf_path, 404);
        abort_unless(Storage::disk('local')->exists($payslip->pdf_path), 404);

        return Storage::disk('local')->response($payslip->pdf_path, $payslip->employee_name.'.pdf', [
            'Content-Disposition' => 'inline; filename="'.addslashes($payslip->employee_name).'.pdf"',
        ]);
    }

    /**
     * Download the payslip PDF.
     */
    public function download(Payslip $payslip)
    {
        abort_if(! $payslip->pdf_path, 404);
        abort_unless(Storage::disk('local')->exists($payslip->pdf_path), 404);

        return Storage::disk('local')->download($payslip->pdf_path, $payslip->employee_name.'.pdf');
    }

    /**
     * Delete a payslip and its stored PDF.
     */
    public function destroy(Payslip $payslip)
    {
        if ($payslip->pdf_path && Storage::disk('local')->exists($payslip->pdf_path)) {
            Storage::disk('local')->delete($payslip->pdf_path);
        }

        $payslip->delete();

        return redirect()->route('pay-allowances.index')->with('success', 'Payslip removed.');
    }
}
