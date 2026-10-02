<?php

namespace App\Http\Controllers;

use App\Models\DisbursementRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DisbursementController extends Controller
{
    /**
     * Disbursement module landing: Records + Reports entry points,
     * plus a snapshot of recent activity.
     */
    public function index()
    {
        $recent = DisbursementRecord::with('user:id,name')
            ->latest('disbursement_date')
            ->latest('id')
            ->take(5)
            ->get();

        $todayTotal = DisbursementRecord::whereDate('disbursement_date', today())->sum('amount');
        $monthTotal = DisbursementRecord::whereYear('disbursement_date', now()->year)
            ->whereMonth('disbursement_date', now()->month)
            ->sum('amount');

        return Inertia::render('Modules/Disbursement/Index', [
            'recent' => $recent,
            'stats' => [
                'today' => (float) $todayTotal,
                'month' => (float) $monthTotal,
                'count' => DisbursementRecord::count(),
            ],
        ]);
    }

    /**
     * Records list with optional filtering.
     */
    public function records(Request $request)
    {
        $filters = $request->only(['search', 'category', 'from', 'to']);

        $records = DisbursementRecord::with('user:id,name')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('payee', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('disbursement_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('disbursement_date', '<=', $to))
            ->latest('disbursement_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Modules/Disbursement/Records', [
            'records' => $records,
            'categories' => DisbursementRecord::CATEGORIES,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the disbursement entry form.
     */
    public function create()
    {
        return Inertia::render('Modules/Disbursement/Create', [
            'categories' => DisbursementRecord::CATEGORIES,
        ]);
    }

    /**
     * Store a new disbursement record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(DisbursementRecord::CATEGORIES)],
            'payee' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:500'],
            'reference_no' => ['required', 'string', 'max:100'],
            'disbursement_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('disbursement/attachments', 'local');
        }

        DisbursementRecord::create([
            'category' => $validated['category'],
            'payee' => $validated['payee'],
            'purpose' => $validated['purpose'],
            'reference_no' => $validated['reference_no'],
            'disbursement_date' => $validated['disbursement_date'],
            'amount' => $validated['amount'],
            'attachment_path' => $path,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('disbursement.records')
            ->with('success', 'Disbursement record saved successfully.');
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
     * Reports: Daily, Monthly, or Date-range, broken down by category.
     */
    public function reports(Request $request)
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(['daily', 'monthly', 'range'])],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $type = $validated['type'] ?? null;
        $results = null;
        $period = null;

        if ($type) {
            [$start, $end, $period] = $this->resolvePeriod($type, $validated);

            $rows = DisbursementRecord::with('user:id,name')
                ->whereBetween('disbursement_date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('disbursement_date')
                ->orderBy('id')
                ->get();

            $results = [
                'rows' => $rows,
                'total' => (float) $rows->sum('amount'),
                'byCategory' => $rows->groupBy('category')->map(fn ($g) => [
                    'count' => $g->count(),
                    'total' => (float) $g->sum('amount'),
                ]),
            ];
        }

        return Inertia::render('Modules/Disbursement/Reports', [
            'type' => $type,
            'period' => $period,
            'results' => $results,
            'preparedBy' => $request->user()->name,
            'generatedAt' => now()->toDateTimeString(),
            'filters' => $validated,
        ]);
    }

    /**
     * Resolve start/end dates and a human label for a report type.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolvePeriod(string $type, array $input): array
    {
        return match ($type) {
            'daily' => (function () use ($input) {
                $day = Carbon::parse($input['date'] ?? today());
                return [$day->copy()->startOfDay(), $day->copy()->endOfDay(), $day->format('F j, Y')];
            })(),
            'monthly' => (function () use ($input) {
                $month = isset($input['month']) ? Carbon::createFromFormat('Y-m', $input['month']) : today();
                return [$month->copy()->startOfMonth(), $month->copy()->endOfMonth(), $month->format('F Y')];
            })(),
            'range' => (function () use ($input) {
                $from = Carbon::parse($input['from'] ?? today());
                $to = Carbon::parse($input['to'] ?? today());
                return [$from->copy()->startOfDay(), $to->copy()->endOfDay(), $from->format('M j, Y').' – '.$to->format('M j, Y')];
            })(),
        };
    }
}
