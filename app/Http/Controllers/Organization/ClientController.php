<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\AssignedTask;
use App\Models\ClientContact;
use App\Models\ClientDocument;
use App\Models\ClientNote;
use App\Models\Job;
use App\Models\Service;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('type', 'C')
            ->where('organization_id', Auth::id());

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');
        
        if (in_array($sort_by, ['name', 'email', 'status', 'created_at'])) {
            $query->orderBy($sort_by, $sort_order);
        }

        $clients = $query->paginate(10);

        if ($request->ajax()) {
            return view('Organization.clients._clients_table', compact('clients', 'sort_by', 'sort_order'))->render();
        }

        return view('Organization.clients.index', compact('clients', 'sort_by', 'sort_order'));
    }

    public function suspended()
    {
        $clients = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->where('status', 'I')
            ->orderBy('name')
            ->paginate(10);

        return view('organization.clients.suspended', compact('clients'));
    }

    public function create()
    {
        return view('organization.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:A,I',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address', 'status']);
        $data['organization_id'] = Auth::id();
        $data['type'] = 'C';
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('clients', 'public');
            $data['photo'] = $path;
        }

        User::create($data);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function edit($id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->with(['contacts', 'notes', 'pinnedNote', 'documents.uploader', 'assignedServices', 'assignedTasks.staff'])
            ->findOrFail($id);
        
        $allServices = Service::where('organization_id', Auth::id())->where('status', 'A')->orderBy('name')->get();
        $allStaff = User::where('organization_id', Auth::id())->whereIn('type', ['T', 'A', 'M', 'O'])->orderBy('name')->get();
        
        $allStaffJson = $allStaff->map(function ($staff) {
            return ['id' => $staff->id, 'text' => $staff->name];
        })->toJson();

        return view('organization.clients.edit', compact('client', 'allServices', 'allStaff', 'allStaffJson'));
    }

    public function update(Request $request, $id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($client->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:A,I',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $client->name = $request->name;
        $client->email = $request->email;
        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->status = $request->status;

        if ($request->hasFile('photo')) {
            if ($client->photo) {
                Storage::disk('public')->delete($client->photo);
            }
            $path = $request->file('photo')->store('clients', 'public');
            $client->photo = $path;
        }

        if ($request->filled('password')) {
            $client->password = Hash::make($request->password);
        }

        $client->save();

        return redirect()->route('clients.edit', $client->id)->with('success', 'Client details updated successfully.');
    }
    
    public function toggleStatus($id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->findOrFail($id);

        $client->status = $client->status === 'A' ? 'I' : 'A';
        $client->save();

        $message = $client->status === 'A' ? 'Client has been activated.' : 'Client has been suspended.';

        return redirect()->back()->with('success', $message);
    }

    public function destroy($id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->findOrFail($id);

        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
    
    public function storeContact(Request $request, User $client)
    {
        if ($client->organization_id !== Auth::id() || $client->type !== 'C') abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
        ]);

        $client->contacts()->create($request->all());
        return redirect()->route('clients.edit', $client->id)->with('success', 'Contact added successfully.');
    }

    public function updateContact(Request $request, ClientContact $contact)
    {
        if ($contact->client->organization_id !== Auth::id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
        ]);

        $contact->update($request->all());
        return redirect()->route('clients.edit', $contact->client_id)->with('success', 'Contact updated successfully.');
    }

    public function destroyContact(ClientContact $contact)
    {
        if ($contact->client->organization_id !== Auth::id()) abort(403);

        $clientId = $contact->client_id;
        $contact->delete();
        return redirect()->route('clients.edit', $clientId)->with('success', 'Contact deleted successfully.');
    }
    
    public function storeNote(Request $request, User $client)
    {
        if ($client->organization_id !== Auth::id()) abort(403);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'note_date' => 'required|date',
        ]);

        $client->notes()->create($request->all());
        return redirect()->route('clients.edit', $client->id)->with('success', 'Note added successfully.');
    }

    public function updateNote(Request $request, ClientNote $note)
    {
        if ($note->client->organization_id !== Auth::id()) abort(403);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'note_date' => 'required|date',
        ]);
        
        $note->update($request->all());
        return redirect()->route('clients.edit', $note->client_id)->with('success', 'Note updated successfully.');
    }
    
    public function destroyNote(ClientNote $note)
    {
        if ($note->client->organization_id !== Auth::id()) abort(403);
        
        $clientId = $note->client_id;
        $note->delete();
        return redirect()->route('clients.edit', $clientId)->with('success', 'Note deleted successfully.');
    }
    
    public function pinNote(ClientNote $note)
    {
        if ($note->client->organization_id !== Auth::id()) abort(403);

        DB::transaction(function () use ($note) {
            ClientNote::where('client_id', $note->client_id)->update(['pinned_at' => null]);
            $note->update(['pinned_at' => now()]);
        });
        
        return redirect()->back()->with('success', 'Note has been pinned.');
    }

    public function unpinNote(ClientNote $note)
    {
        if ($note->client->organization_id !== Auth::id()) abort(403);

        $note->update(['pinned_at' => null]);
        
        return redirect()->back()->with('success', 'Note has been unpinned.');
    }

    public function storeDocument(Request $request, User $client)
    {
        if ($client->organization_id !== Auth::id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'document_file' => 'required|file|mimes:jpg,png,pdf,docx|max:10240',
        ]);

        $file = $request->file('document_file');
        $filePath = $file->store('client_documents', 'public');

        $client->documents()->create([
            'name' => $request->name,
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'uploaded_by_id' => Auth::id(),
        ]);

        return redirect()->route('clients.edit', $client->id)->with('success', 'Document uploaded successfully.');
    }

    public function destroyDocument(ClientDocument $document)
    {
        if ($document->client->organization_id !== Auth::id()) abort(403);
        
        Storage::disk('public')->delete($document->file_path);
        
        $clientId = $document->client_id;
        $document->delete();

        return redirect()->route('clients.edit', $clientId)->with('success', 'Document deleted successfully.');
    }

    public function downloadDocument(ClientDocument $document)
    {
        if ($document->client->organization_id !== Auth::id()) abort(403);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->withErrors(['msg' => 'File not found.']);
        }

        $originalName = $document->name . '.' . $document->file_type;

        return Storage::disk('public')->download($document->file_path, $originalName);
    }
    
    public function getJobsForServiceAssignment(Request $request)
    {
        $serviceIds = $request->input('service_ids', []);
        if (empty($serviceIds)) {
            return response()->json([]);
        }

        $organizationId = Auth::id();

        $jobs = Job::whereIn('service_id', $serviceIds)
                   ->whereHas('service', function ($query) use ($organizationId) {
                       $query->where('organization_id', $organizationId);
                   })
                   ->with('tasks')
                   ->orderBy('name')
                   ->get();
        
        return response()->json($jobs);
    }

    public function assignServices(Request $request, User $client)
    {
        if ($client->organization_id !== Auth::id()) abort(403);

        $validated = $request->validate([
            'services' => 'sometimes|array',
            'services.*' => 'exists:services,id',
            'tasks' => 'present|array',
            'staff_assignments' => 'present|array',
        ]);

        DB::transaction(function () use ($validated, $client) {
            $client->assignedServices()->sync($validated['services'] ?? []);
            
            $currentTaskIds = $client->assignedTasks()->pluck('task_template_id')->toArray();
            $newTaskIds = array_keys($validated['tasks'] ?? []);

            $tasksToDelete = array_diff($currentTaskIds, $newTaskIds);
            if (!empty($tasksToDelete)) {
                $client->assignedTasks()->whereIn('task_template_id', $tasksToDelete)->delete();
            }

            if (!empty($newTaskIds)) {
                $selectedTaskTemplates = Task::with('job.service')->findMany($newTaskIds);
                
                foreach ($selectedTaskTemplates as $taskTemplate) {

                    $assignedTask = AssignedTask::updateOrCreate(
                        [
                            'client_id' => $client->id,
                            'task_template_id' => $taskTemplate->id,
                        ],
                        [
                            'service_id' => $taskTemplate->job->service_id,
                            'job_id' => $taskTemplate->job_id,
                            'name' => $taskTemplate->name,
                            'description' => $taskTemplate->description,
                            'status' => 'pending',
                            'start' => $taskTemplate->start, // Direct copy
                            'end' => $taskTemplate->end,     // Direct copy
                            'is_recurring' => $taskTemplate->is_recurring,
                            'recurring_frequency' => $taskTemplate->recurring_frequency,
                        ]
                    );

                    if (isset($validated['staff_assignments'][$taskTemplate->id])) {
                        $staffIds = $validated['staff_assignments'][$taskTemplate->id];
                        $assignedTask->staff()->sync($staffIds);
                    } else {
                        $assignedTask->staff()->detach();
                    }
                }
            }
        });

        return redirect()->route('clients.edit', $client->id)->with('success', 'Client services and tasks have been updated successfully.');
    }
}