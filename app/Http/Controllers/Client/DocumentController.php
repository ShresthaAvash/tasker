<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $client = Auth::user();
        $documents = ClientDocument::where('client_id', $client->id)
            ->with('uploader')
            ->latest()
            ->paginate(10);
            
        return view('Client.documents', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_file' => 'required|file|mimes:jpg,jpeg,png,pdf,docx,xlsx|max:10240', // 10MB Max
        ]);

        $client = Auth::user();
        $file = $request->file('document_file');
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

        return redirect()->route('client.documents.index')->with('success', 'Document uploaded successfully.');
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