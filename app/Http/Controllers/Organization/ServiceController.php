<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ✅ MODIFIED: Added with('jobs') to eager load the relationship
        $query = Service::where('organization_id', Auth::id())->with('jobs');

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');
        if (in_array($sort_by, ['name', 'status', 'created_at'])) {
            $query->orderBy($sort_by, $sort_order);
        }

        $services = $query->paginate(10);
        
        if ($request->ajax()) {
            return view('Organization.services._services_table', compact('services', 'sort_by', 'sort_order'))->render();
        }
        
        return view('Organization.services.index', compact('services', 'sort_by', 'sort_order'));
    }

    // ... The rest of the controller remains exactly the same ...
    
    /**
     * Display a list of suspended services.
     */
    public function suspended()
    {
        $services = Service::where('organization_id', Auth::id())
            ->where('status', 'I') // Only Inactive/Suspended
            ->orderBy('name')
            ->paginate(10);

        return view('organization.services.suspended', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('organization.services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('services')->where('organization_id', Auth::id()),
            ],
            'description' => 'nullable|string',
            'status' => 'required|in:A,I',
        ]);

        Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'organization_id' => Auth::id(),
        ]);

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }
    
    /**
     * Display the specified resource for building the template.
     */
    public function show(Service $service)
    {
        if ($service->organization_id !== Auth::id()) {
            abort(403);
        }

        // Eager load all relationships for the builder
        $service->load('jobs.tasks.designation');
        
        $designations = \App\Models\StaffDesignation::where('organization_id', Auth::id())->get();

        return view('Organization.services.show', compact('service', 'designations'));
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        if ($service->organization_id !== Auth::id()) {
            abort(403);
        }
        return view('organization.services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        if ($service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('services')->where('organization_id', Auth::id())->ignore($service->id),
            ],
            'description' => 'nullable|string',
            'status' => 'required|in:A,I',
        ]);

        $service->update($request->all());

        return redirect()->route('services.index')->with('success', 'Service updated successfully.');
    }
    
    /**
     * Toggle the status of a service between Active and Suspended.
     */
    public function toggleStatus(Service $service)
    {
        if ($service->organization_id !== Auth::id()) {
            abort(403);
        }

        $service->status = $service->status === 'A' ? 'I' : 'A';
        $service->save();

        $message = $service->status === 'A' ? 'Service has been activated.' : 'Service has been suspended.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        if ($service->organization_id !== Auth::id()) {
            abort(403);
        }

        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service deleted successfully.');
    }
}