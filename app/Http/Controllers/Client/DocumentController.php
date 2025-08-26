<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $client = Auth::user();
        $search = $request->input('search');
        $types = $request->input('types', []);
        $uploadedBy = $request->input('uploaded_by');
        [$startDate, $endDate] = $this->resolveDatesFromRequest($request);

        $documentsQuery = ClientDocument::where('client_id', $client->id)
            ->with('uploader');

        if ($search) {
            $documentsQuery->where('name', 'like', '%' . $search . '%');
        }

        if (!empty($types) && is_array($types)) {
            $documentsQuery->whereIn('file_type', $types);
        }

        if ($uploadedBy) {
            if ($uploadedBy === 'client') {
                $documentsQuery->where('uploaded_by_id', $client->id);
            } elseif ($uploadedBy === 'organization') {
                $documentsQuery->where('uploaded_by_id', '!=', $client->id);
            }
        }

        $documentsQuery->whereBetween('created_at', [$startDate, $endDate]);

        $documents = $documentsQuery->latest()->paginate(10);

        $availableFileTypes = [
            ['id' => 'pdf', 'text' => 'PDF'],
            ['id' => 'docx', 'text' => 'DOCX'],
            ['id' => 'xlsx', 'text' => 'XLSX'],
            ['id' => 'png', 'text' => 'PNG'],
            ['id' => 'jpg', 'text' => 'JPG'],
            ['id' => 'jpeg', 'text' => 'JPEG'],
        ];

        if ($request->ajax()) {
            return view('Client._documents_table', compact('documents'))->render();
        }
        
        $years = range(now()->year - 4, now()->year + 2);
        $months = ['all' => 'All Months'];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
        }

        return view('Client.documents', [
            'documents' => $documents,
            'availableFileTypes' => $availableFileTypes,
            'search' => $search,
            'selectedTypes' => $types,
            'selectedUploadedBy' => $uploadedBy,
            'years' => $years,
            'months' => $months,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'use_custom_range' => $request->get('use_custom_range') === 'true',
            'currentYear' => $request->get('year', now()->year),
            'currentMonth' => $request->get('month', 'all'),
        ]);
    }

    private function resolveDatesFromRequest(Request $request): array
    {
        if ($request->get('use_custom_range') === 'true') {
            $start = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfDay();
            $end = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();
            return [$start, $end];
        }
        
        $year = $request->get('year', now()->year);
        $month = $request->get('month', 'all');

        if ($month === 'all' || $month === null) {
            return [Carbon::create($year)->startOfYear(), Carbon::create($year)->endOfYear()];
        }
        
        $startDate = Carbon::create($year, (int)$month, 1)->startOfMonth();
        return [$startDate, $startDate->copy()->endOfMonth()];
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_file' => 'required|array|min:1',
            'document_file.*' => 'required|file|mimes:jpg,jpeg,png,pdf,docx,xlsx|max:10240', // 10MB Max per file
        ]);

        $client = Auth::user();
        
        foreach ($request->file('document_file') as $file) {
            $filePath = $file->store('client_documents/' . $client->id, 'public');

            ClientDocument::create([
                'client_id' => $client->id,
                'uploaded_by_id' => $client->id, // Client is the uploader
                'name' => $request->name,
                'description' => $request->description,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->route('client.documents.index')->with('success', 'Documents uploaded successfully.');
    }

    public function download(ClientDocument $document)
    {
        // Authorize: ensure the document belongs to the logged-in client
        if ($document->client_id !== Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->withErrors(['msg' => 'File not found.']);
        }

        $originalName = $document->name . '.' . $document->file_type;

        return Storage::disk('public')->download($document->file_path, $originalName);
    }

    public function destroy(ClientDocument $document)
    {
        // Authorize: ensure the document was uploaded by the logged-in client
        if ($document->uploaded_by_id !== Auth::id()) {
            abort(403, 'You can only delete documents that you have uploaded.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('client.documents.index')->with('success', 'Document deleted successfully.');
    }
}