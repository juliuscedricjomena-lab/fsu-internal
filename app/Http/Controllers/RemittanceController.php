<?php

namespace App\Http\Controllers;

use App\Models\RemittanceFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RemittanceController extends Controller
{
    /**
     * List uploaded remittance files, with optional type/date filtering.
     * Files are sorted ascending by upload date (per Slide 10).
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'date']);

        $files = RemittanceFile::with('user:id,name')
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->orderBy('created_at') // ascending by upload date
            ->get();

        return Inertia::render('Modules/Remittance/Index', [
            'files' => $files,
            'types' => RemittanceFile::TYPES,
            'filters' => $filters,
        ]);
    }

    /**
     * Upload a new remittance file.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(RemittanceFile::TYPES)],
            'file' => ['required', 'file', 'mimes:pdf,xls,xlsx,csv,jpg,jpeg,png', 'max:10240'],
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('remittance', 'local');

        RemittanceFile::create([
            'type' => $validated['type'],
            'original_name' => $uploaded->getClientOriginalName(),
            'stored_path' => $path,
            'size' => $uploaded->getSize(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('remittance.index')
            ->with('success', 'Successfully Uploaded!');
    }

    /**
     * Download a remittance file (authenticated users only).
     */
    public function download(RemittanceFile $remittance)
    {
        abort_unless(Storage::disk('local')->exists($remittance->stored_path), 404);

        return Storage::disk('local')->download($remittance->stored_path, $remittance->original_name);
    }

    /**
     * Discard (delete) a remittance file and its stored copy.
     */
    public function destroy(RemittanceFile $remittance)
    {
        if (Storage::disk('local')->exists($remittance->stored_path)) {
            Storage::disk('local')->delete($remittance->stored_path);
        }

        $remittance->delete();

        return redirect()->route('remittance.index')
            ->with('success', 'File discarded.');
    }
}
