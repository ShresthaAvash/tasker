<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ClientDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Get a list of clients the staff member is associated with.
     * An association exists if the staff is assigned to any of the client's tasks.
     */
    private function getAssociatedClients()
    {
        $staffId = Auth::id();
        $organizationId = Auth::user()->organization_id;

        // Find client IDs from tasks assigned to this staff member
        $clientIds = \App\Models\AssignedTask::whereHas('staff', function ($q) use ($staffId) {
                $q->where('users.id', $staffId);
            })
            ->distinct()
            ->pluck('client_id');
            
        // Get the client User models
        return User::whereIn('id', $clientIds)
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Display the document management page for staff.
     */
    public function index(Request $request)
    {
        $clients = $this->getAssociatedClients();
        $selectedClient = null;
        $documents = collect();

        if ($request->filled('client_id')) {
            $selectedClient = User::find($request->client_id);
            // Authorize that this staff member can view this client's documents
            if ($selectedClient && $clients->contains($selectedClient)) {
                 $documents = ClientDocument::where('client_id', $selectedClient->id)
                    ->with('uploader')
                    ->latest()
                    ->paginate(10);
            } else {
                // If they try to access a client they shouldn't, reset selection
                $selectedClient = null; 
            }
        }

        return view('Staff.documents', compact('clients', 'selectedClient', 'documents'));
    }

    /**
     * Store a new document uploaded by a staff member for a client.
     */
    public function store(Request $request, User $client)
    {
        // Authorize: Ensure the staff member has access to this client.
        $clients = $this->getAssociatedClients();
        if (!$clients->contains($client)) {
            abort(403, 'You are not authorized to upload documents for this client.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_file' => 'required|array|min:1',
            'document_file.*' => 'required|file|mimes:jpg,jpeg,png,pdf,docx,xlsx|max:10240', // 10MB Max per file
        ]);

        $staff = Auth::user();
        
        foreach ($request->file('document_file') as $file) {
            $filePath = $file->store('client_documents/' . $client->id, 'public');

            ClientDocument::create([
                'client_id' => $client->id,
                'uploaded_by_id' => $staff->id, // Staff is the uploader
                'name' => $request->name,
                'description' => $request->description,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->route('staff.documents.index', ['client_id' => $client->id])->with('success', 'Documents uploaded successfully.');
    }

    /**
     * Download a specific document.
     */
    public function download(ClientDocument $document)
    {
        // Authorize: Ensure staff can access the client this document belongs to.
        $client = $document->client;
        $clients = $this->getAssociatedClients();
        if (!$clients->contains($client)) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->withErrors(['msg' => 'File not found.']);
        }

        $originalName = $document->name . '.' . $document->file_type;

        return Storage::disk('public')->download($document->file_path, $originalName);
    }

    /**
     * Destroy a document.
     */
    public function destroy(ClientDocument $document)
    {
        // Authorize: Staff can only delete documents they uploaded.
        if ($document->uploaded_by_id !== Auth::id()) {
            abort(403, 'You can only delete documents that you have uploaded.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}