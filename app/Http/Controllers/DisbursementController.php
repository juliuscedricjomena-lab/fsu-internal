<?php

namespace App\Http\Controllers;

use App\Models\DisbursementRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DisbursementController extends Controller
{
    /**
     * Landing: snapshot + navigation to Records, Budget Matrix, Particulars.
     */
    public function index()
    {
        $year = (int) now()->year;

        $recent = DisbursementRecord::with('user:id,name')
            ->latest('disbursement_date')
            ->latest('id')
            ->take(5)
            ->get();

        $yearTotal = DisbursementRecord::whereYear('disbursement_date', $year)->sum('amount');
        $monthTotal = DisbursementRecord::whereYear('disbursement_date', $year)
            ->whereMonth('disbursement_date', now()->month)
            ->sum('amount');

        return Inertia::render('Modules/Disbursement/Index', [
            'recent' => $recent,
            'stats' => [
                'year' => (float) $yearTotal,
                'month' => (float) $monthTotal,
                'count' => DisbursementRecord::count(),
            ],
            'currentYear' => $year,
        ]);
    }

    /**
     * Records list with optional filtering.
     */
    public function records(Request $request)
    {
        $filters = $request->only(['search', 'fund_type', 'expense_group', 'from', 'to']);

        $records = DisbursementRecord::with('user:id,name')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('payee', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhere('project_particular', 'like', "%{$search}%")
                        ->orWhere('expense_class', 'like', "%{$search}%");
                });
            })
            ->when($filters['fund_type'] ?? null, fn ($q, $v) => $q->where('fund_type', $v))
            ->when($filters['expense_group'] ?? null, fn ($q, $v) => $q->where('expense_group', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('disbursement_date', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('disbursement_date', '<=', $v))
            ->latest('disbursement_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Modules/Disbursement/Records', [
            'records' => $records,
            'fundTypes' => DisbursementRecord::FUND_TYPES,
            'expenseGroups' => DisbursementRecord::EXPENSE_GROUPS,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the disbursement entry form.
     */
    public function create()
    {
        return Inertia::render('Modules/Disbursement/Create', [
            'fundTypes' => DisbursementRecord::FUND_TYPES,
            'expenseGroups' => DisbursementRecord::EXPENSE_GROUPS,
            'expenseClasses' => DisbursementRecord::EXPENSE_CLASSES,
            'groupsByFund' => DisbursementRecord::GROUPS_BY_FUND,
            'modes' => DisbursementRecord::MODES_OF_DISBURSEMENT,
        ]);
    }

    /**
     * Store a new disbursement record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fund_type' => ['required', Rule::in(array_keys(DisbursementRecord::FUND_TYPES))],
            'expense_group' => ['required', Rule::in(array_keys(DisbursementRecord::EXPENSE_GROUPS))],
            'expense_class' => ['required', 'string', 'max:255'],
            'project_particular' => ['nullable', 'string', 'max:1000'],
            'payee' => ['required', 'string', 'max:255'],
            'reference_no' => ['required', 'string', 'max:100'],
            'disbursement_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'mode_of_disbursement' => ['nullable', 'string', 'max:100'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('disbursement/attachments', 'local');
        }

        DisbursementRecord::create([
            'fund_type' => $validated['fund_type'],
            'expense_group' => $validated['expense_group'],
            'expense_class' => $validated['expense_class'],
            'project_particular' => $validated['project_particular'] ?? null,
            'payee' => $validated['payee'],
            'reference_no' => $validated['reference_no'],
            'disbursement_date' => $validated['disbursement_date'],
            'amount' => $validated['amount'],
            'mode_of_disbursement' => $validated['mode_of_disbursement'] ?? null,
            'attachment_path' => $path,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('disbursement.records')
            ->with('success', 'Disbursement record saved successfully.');
    }

    /**
     * Show the edit form for an existing disbursement record.
     */
    public function edit(DisbursementRecord $disbursement)
    {
        return Inertia::render('Modules/Disbursement/Edit', [
            'record' => $disbursement,
            'fundTypes' => DisbursementRecord::FUND_TYPES,
            'expenseGroups' => DisbursementRecord::EXPENSE_GROUPS,
            'expenseClasses' => DisbursementRecord::EXPENSE_CLASSES,
            'groupsByFund' => DisbursementRecord::GROUPS_BY_FUND,
            'modes' => DisbursementRecord::MODES_OF_DISBURSEMENT,
        ]);
    }

    /**
     * Update an existing disbursement record.
     */
    public function update(Request $request, DisbursementRecord $disbursement)
    {
        $validated = $request->validate([
            'fund_type' => ['required', Rule::in(array_keys(DisbursementRecord::FUND_TYPES))],
            'expense_group' => ['required', Rule::in(array_keys(DisbursementRecord::EXPENSE_GROUPS))],
            'expense_class' => ['required', 'string', 'max:255'],
            'project_particular' => ['nullable', 'string', 'max:1000'],
            'payee' => ['required', 'string', 'max:255'],
            'reference_no' => ['required', 'string', 'max:100'],
            'disbursement_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'mode_of_disbursement' => ['nullable', 'string', 'max:100'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'remove_attachment' => ['nullable', 'boolean'],
        ]);

        $path = $disbursement->attachment_path;

        // Replace the attachment if a new one is uploaded; or remove it if asked.
        if ($request->hasFile('attachment')) {
            if ($path && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
            $path = $request->file('attachment')->store('disbursement/attachments', 'local');
        } elseif ($request->boolean('remove_attachment') && $path) {
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
            $path = null;
        }

        $disbursement->update([
            'fund_type' => $validated['fund_type'],
            'expense_group' => $validated['expense_group'],
            'expense_class' => $validated['expense_class'],
            'project_particular' => $validated['project_particular'] ?? null,
            'payee' => $validated['payee'],
            'reference_no' => $validated['reference_no'],
            'disbursement_date' => $validated['disbursement_date'],
            'amount' => $validated['amount'],
            'mode_of_disbursement' => $validated['mode_of_disbursement'] ?? null,
            'attachment_path' => $path,
        ]);

        return redirect()->route('disbursement.records')
            ->with('success', 'Disbursement record updated successfully.');
    }

    /**
     * Stream an attachment to authenticated users only.
     */
    public function attachment(DisbursementRecord $disbursement)
    {
        abort_if(! $disbursement->attachment_path, 404);
        abort_unless(Storage::disk('local')->exists($disbursement->attachment_path), 404);

        return Storage::disk('local')->response($disbursement->attachment_path);
    }

    /**
     * Budget Matrix (slide 7): expense-class rows x 12 month columns, with
     * sub-totals per expense group and a grand total, for a given fund type/year.
     */
    public function matrix(Request $request)
    {
        $year = (int) $request->integer('year', now()->year);
        $fundType = $request->string('fund_type')->toString() ?: 'appropriated';
        if (! array_key_exists($fundType, DisbursementRecord::FUND_TYPES)) {
            $fundType = 'appropriated';
        }

        // Pull monthly sums grouped by expense_group + expense_class.
        $rows = DisbursementRecord::query()
            ->where('fund_type', $fundType)
            ->whereYear('disbursement_date', $year)
            ->get(['expense_group', 'expense_class', 'disbursement_date', 'amount']);

        // Build the matrix skeleton from the known class hierarchy so empty
        // classes still render (matching the printed worksheet).
        $groups = DisbursementRecord::GROUPS_BY_FUND[$fundType];
        $matrix = [];
        foreach ($groups as $groupCode) {
            $classes = DisbursementRecord::EXPENSE_CLASSES[$groupCode] ?? [];
            $matrix[$groupCode] = [
                'label' => DisbursementRecord::EXPENSE_GROUPS[$groupCode],
                'classes' => collect($classes)->mapWithKeys(fn ($c) => [$c => array_fill(1, 12, 0.0)])->toArray(),
            ];
        }

        // Fold actual records into the matrix (also capture ad-hoc classes
        // not in the predefined list).
        foreach ($rows as $r) {
            $month = (int) $r->disbursement_date->format('n');
            $g = $r->expense_group;
            $c = $r->expense_class;
            if (! isset($matrix[$g])) {
                $matrix[$g] = ['label' => DisbursementRecord::EXPENSE_GROUPS[$g] ?? $g, 'classes' => []];
            }
            if (! isset($matrix[$g]['classes'][$c])) {
                $matrix[$g]['classes'][$c] = array_fill(1, 12, 0.0);
            }
            $matrix[$g]['classes'][$c][$month] += (float) $r->amount;
        }

        // Compute per-group subtotals and the grand total.
        $grand = array_fill(1, 12, 0.0);
        $grandTotal = 0.0;
        $output = [];
        foreach ($matrix as $groupCode => $group) {
            $subtotal = array_fill(1, 12, 0.0);
            $classesOut = [];
            foreach ($group['classes'] as $class => $months) {
                $rowTotal = array_sum($months);
                foreach ($months as $m => $val) {
                    $subtotal[$m] += $val;
                    $grand[$m] += $val;
                }
                $classesOut[] = ['class' => $class, 'months' => $months, 'total' => $rowTotal];
            }
            $subtotalSum = array_sum($subtotal);
            $grandTotal += $subtotalSum;
            $output[] = [
                'code' => $groupCode,
                'label' => $group['label'],
                'classes' => $classesOut,
                'subtotal' => $subtotal,
                'subtotalSum' => $subtotalSum,
            ];
        }

        return Inertia::render('Modules/Disbursement/Matrix', [
            'groups' => $output,
            'grand' => $grand,
            'grandTotal' => $grandTotal,
            'fundType' => $fundType,
            'fundTypes' => DisbursementRecord::FUND_TYPES,
            'year' => $year,
            'preparedBy' => $request->user()->name,
            'generatedAt' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Capital Outlay particulars (slide 8): CO records grouped by month, each
     * showing Projects/Particulars, Payee, Amount, and Mode of Disbursement.
     */
    public function particulars(Request $request)
    {
        $year = (int) $request->integer('year', now()->year);

        $records = DisbursementRecord::query()
            ->where('expense_group', 'CO')
            ->whereYear('disbursement_date', $year)
            ->orderBy('disbursement_date')
            ->orderBy('id')
            ->get(['disbursement_date', 'project_particular', 'payee', 'amount', 'mode_of_disbursement']);

        $byMonth = $records
            ->groupBy(fn ($r) => $r->disbursement_date->format('F Y'))
            ->map(fn ($group) => [
                'rows' => $group->map(fn ($r) => [
                    'particular' => $r->project_particular,
                    'payee' => $r->payee,
                    'amount' => (float) $r->amount,
                    'mode' => $r->mode_of_disbursement,
                ])->values(),
                'total' => (float) $group->sum('amount'),
            ]);

        return Inertia::render('Modules/Disbursement/Particulars', [
            'byMonth' => $byMonth,
            'grandTotal' => (float) $records->sum('amount'),
            'year' => $year,
            'preparedBy' => $request->user()->name,
            'generatedAt' => now()->toDateTimeString(),
        ]);
    }
}
