<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\AssignedTask;
use App\Models\ClientContact;
use App\Models\ClientDocument;
use App\Models\ClientNote;
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
use App\Notifications\ClientTaskAssigned;
use App\Notifications\MessageFromOrganization;
use App\Notifications\MessageSentToClients;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('type', 'C')
            ->where('organization_id', Auth::id());

        // --- THIS IS THE FIX: Filter by an array of statuses if provided ---
        $statuses = $request->get('statuses');
        if (!empty($statuses) && is_array($statuses)) {
            $query->whereIn('status', $statuses);
        }

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
        
        $organizationId = Auth::id();
        $allServices = Service::where('organization_id', $organizationId)
            ->where('status', 'A')
            ->where(function ($query) use ($id) {
                $query->whereNull('client_id') 
                      ->orWhere('client_id', $id);
            })
            ->orderBy('name')
            ->get();
        
        $allStaff = User::where('organization_id', Auth::id())->whereIn('type', ['T', 'A', 'M'])->orderBy('name')->get();
        $designations = \App\Models\StaffDesignation::where('organization_id', Auth::id())->orderBy('name')->get();
        
        $allStaffJson = $allStaff->map(function ($staff) {
            return ['id' => $staff->id, 'text' => $staff->name];
        })->toJson();

        return view('organization.clients.edit', compact('client', 'allServices', 'allStaff', 'allStaffJson', 'designations'));
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

        // --- THIS IS THE FIX: Updated wording ---
        $message = $client->status === 'A' ? 'Client has been activated.' : 'Client has been made inactive.';

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
            'description' => 'nullable|string|max:1000',
            'document_file' => 'required|array|min:1',
            'document_file.*' => 'file|mimes:jpg,png,pdf,docx,xlsx|max:10240', // Validate each file
        ]);

        foreach ($request->file('document_file') as $file) {
            $filePath = $file->store('client_documents/' . $client->id, 'public');

            $client->documents()->create([
                'name' => $request->name,
                'description' => $request->description,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'uploaded_by_id' => Auth::id(),
            ]);
        }

        return redirect()->route('clients.edit', $client->id)->with('success', 'Documents uploaded successfully.');
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
    
    public function getTasksForServiceAssignment(Request $request)
    {
        $serviceIds = $request->input('service_ids', []);
        if (empty($serviceIds)) {
            return response()->json([]);
        }

        $organizationId = Auth::id();

        $tasks = Task::whereIn('service_id', $serviceIds)
                   ->whereHas('service', function ($query) use ($organizationId) {
                       $query->where('organization_id', $organizationId);
                   })
                   ->with(['service', 'staff']) 
                   ->orderBy('name')
                   ->get();
        
        return response()->json($tasks);
    }

    public function assignServices(Request $request, User $client)
    {
        if ($client->organization_id !== Auth::id()) abort(403);
    
        $validated = $request->validate([
            'services' => 'sometimes|array',
            'services.*' => 'exists:services,id',
            'tasks' => 'present|array',
            'staff_assignments' => 'present|array',
            'task_start_dates' => 'sometimes|array',
            'task_start_dates.*' => 'nullable|date',
            'task_end_dates' => 'sometimes|array',
            'task_end_dates.*' => 'nullable|date|after_or_equal:task_start_dates.*',
        ]);
    
        $selectedTaskIds = array_keys($validated['tasks'] ?? []);
        if (!empty($selectedTaskIds)) {
            $tasksNeedingDates = Task::whereIn('id', $selectedTaskIds)
                                      ->whereNull('start')
                                      ->get();
    
            foreach ($tasksNeedingDates as $task) {
                if (empty($validated['task_start_dates'][$task->id])) {
                    throw ValidationException::withMessages([
                        'task_start_dates' => "A start date is required for the task '{$task->name}' because it does not have a default start date.",
                    ]);
                }
            }
        }
    
        DB::transaction(function () use ($validated, $client) {
            $currentTaskIds = $client->assignedTasks()->pluck('task_template_id')->toArray();
            $newTaskIds = array_keys($validated['tasks'] ?? []);
    
            $tasksToDelete = array_diff($currentTaskIds, $newTaskIds);
            if (!empty($tasksToDelete)) {
                $client->assignedTasks()->whereIn('task_template_id', $tasksToDelete)->delete();
            }
            
            $assignedServiceIds = [];

            if (!empty($newTaskIds)) {
                $selectedTaskTemplates = Task::with('service')->findMany($newTaskIds);
                
                foreach ($selectedTaskTemplates as $taskTemplate) {
                    $service = $taskTemplate->service;
                    if ($service) {
                        $assignedServiceIds[] = $service->id;
                    }
                    
                    $startDate = $validated['task_start_dates'][$taskTemplate->id] ?? $taskTemplate->start;
                    $endDate = $validated['task_end_dates'][$taskTemplate->id] ?? $taskTemplate->end;

                    $assignedTask = AssignedTask::firstOrNew(
                        ['client_id' => $client->id, 'task_template_id' => $taskTemplate->id]
                    );

                    if (!$assignedTask->exists) {
                        $assignedTask->status = 'to_do';
                    }

                    $assignedTask->fill([
                        'service_id' => $taskTemplate->service_id,
                        'name' => $taskTemplate->name,
                        'description' => $taskTemplate->description,
                        'start' => $startDate,
                        'end' => $endDate,
                        'is_recurring' => $service->is_recurring,
                        'recurring_frequency' => $service->recurring_frequency,
                    ])->save();
    
                    $staffIds = $validated['staff_assignments'][$taskTemplate->id] ?? [];
                    
                    $syncResult = $assignedTask->staff()->sync($staffIds);
                    $newlyAttachedStaffIds = $syncResult['attached'];
    
                    if (!empty($newlyAttachedStaffIds)) {
                        $newlyAssignedStaff = User::find($newlyAttachedStaffIds);
                        
                        foreach ($newlyAssignedStaff as $staffMember) {
                            $staffMember->notify(new ClientTaskAssigned($assignedTask));
                        }
                    }
                }
            }

            // Sync services based only on the ones that have tasks assigned
            $client->assignedServices()->sync(array_unique($assignedServiceIds));
        });
    
        return redirect()->route('clients.edit', $client->id)->with('success', 'Client services and tasks have been updated successfully.');
    }

    public function storeClientSpecificService(Request $request, User $client) {
        if ($client->organization_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('services')->where('organization_id', Auth::id())],
            'description' => 'nullable|string',
            'tasks' => 'present|array',
            'tasks.*.name' => 'required|string|max:255',
            'tasks.*.start' => 'nullable|date',
            'is_recurring' => 'sometimes|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service = DB::transaction(function () use ($request, $client) {
            $service = Service::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'organization_id' => Auth::id(),
                'client_id' => $client->id,
                'status' => 'A',
                'is_recurring' => $request->has('is_recurring'),
                'recurring_frequency' => $request->input('recurring_frequency'),
            ]);

            foreach ($request->input('tasks', []) as $taskData) {
                $service->tasks()->create([
                    'name' => $taskData['name'],
                    'description' => $taskData['description'] ?? null,
                    'start' => $taskData['start'],
                    'end' => $taskData['end'] ?? null,
                    'staff_designation_id' => $taskData['staff_designation_id'] ?? null,
                    'status' => 'not_started'
                ]);
            }
            return $service;
        });

        return response()->json([
            'success' => 'Service created successfully!',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
            ]
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $organization = Auth::user();
        $clients = User::whereIn('id', $request->client_ids)
            ->where('organization_id', $organization->id) // Security check
            ->get();

        if ($clients->isEmpty()) {
            return back()->with('error', 'No valid clients were selected.');
        }

        Notification::send($clients, new MessageFromOrganization(
            $organization,
            $request->subject,
            $request->message
        ));

        // Send a confirmation notification to the organization
        $organization->notify(new MessageSentToClients(
            $organization,
            $request->subject,
            $request->message,
            $clients
        ));

        return back()->with('success', 'Message sent successfully to ' . $clients->count() . ' client(s).');
    }
}