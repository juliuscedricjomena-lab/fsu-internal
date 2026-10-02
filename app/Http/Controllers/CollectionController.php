<?php

namespace App\Http\Controllers;

use App\Models\CollectionTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CollectionController extends Controller
{
    /**
     * Collection module landing: Transactions + Reports entry points,
     * plus a quick snapshot of recent activity.
     */
    public function index()
    {
        $recent = CollectionTransaction::with('user:id,name')
            ->latest('transaction_date')
            ->latest('id')
            ->take(5)
            ->get();

        $todayTotal = CollectionTransaction::whereDate('transaction_date', today())->sum('amount');
        $monthTotal = CollectionTransaction::whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        return Inertia::render('Modules/Collection/Index', [
            'recent' => $recent,
            'stats' => [
                'today' => (float) $todayTotal,
                'month' => (float) $monthTotal,
                'count' => CollectionTransaction::count(),
            ],
        ]);
    }

    /**
     * Transactions list with optional filtering.
     */
    public function transactions(Request $request)
    {
        $filters = $request->only(['search', 'account_type', 'from', 'to']);

        $transactions = CollectionTransaction::with('user:id,name')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('payor_name', 'like', "%{$search}%")
                        ->orWhere('or_number', 'like', "%{$search}%");
                });
            })
            ->when($filters['account_type'] ?? null, fn ($q, $type) => $q->where('account_type', $type))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('transaction_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('transaction_date', '<=', $to))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Modules/Collection/Transactions', [
            'transactions' => $transactions,
            'accountTypes' => CollectionTransaction::ACCOUNT_TYPES,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the transaction entry form.
     */
    public function create()
    {
        return Inertia::render('Modules/Collection/Create', [
            'accountTypes' => CollectionTransaction::ACCOUNT_TYPES,
        ]);
    }

    /**
     * Store a new collection transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_type' => ['required', Rule::in(CollectionTransaction::ACCOUNT_TYPES)],
            'payor_name' => ['required', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
            'or_number' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'or_attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Store the official receipt attachment privately (receipts are sensitive).
        $path = null;
        if ($request->hasFile('or_attachment')) {
            $path = $request->file('or_attachment')->store('collection/receipts', 'local');
        }

        CollectionTransaction::create([
            'account_type' => $validated['account_type'],
            'payor_name' => $validated['payor_name'],
            'transaction_date' => $validated['transaction_date'],
            'or_number' => $validated['or_number'],
            'amount' => $validated['amount'],
            'or_attachment_path' => $path,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('collection.transactions')
            ->with('success', 'Collection transaction recorded successfully.');
    }

    /**
     * Stream a receipt attachment to authenticated users only.
     */
    public function attachment(CollectionTransaction $transaction)
    {
        abort_if(! $transaction->or_attachment_path, 404);
        abort_unless(Storage::disk('local')->exists($transaction->or_attachment_path), 404);

        return Storage::disk('local')->response($transaction->or_attachment_path);
    }

    /**
     * Reports: Daily, Monthly, or Date-range (per Slide 17).
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

            $query = CollectionTransaction::with('user:id,name')
                ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('transaction_date')
                ->orderBy('id');

            $rows = $query->get();

            $results = [
                'rows' => $rows,
                'total' => (float) $rows->sum('amount'),
                'byAccountType' => $rows->groupBy('account_type')->map(fn ($g) => [
                    'count' => $g->count(),
                    'total' => (float) $g->sum('amount'),
                ]),
            ];
        }

        return Inertia::render('Modules/Collection/Reports', [
            'type' => $type,
            'period' => $period,
            'results' => $results,
            'preparedBy' => $request->user()->name,
            'generatedAt' => now()->toDateTimeString(),
            'filters' => $validated,
        ]);
    }

    /**
     * Resolve the start/end dates and a human-readable label for a report type.
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
