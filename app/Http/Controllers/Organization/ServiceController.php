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
        $query = Service::where('organization_id', Auth::id())->with('jobs');

        // --- THIS IS THE FIX: Filter by an array of statuses if provided ---
        $statuses = $request->get('statuses');
        if (!empty($statuses) && is_array($statuses)) {
            $query->whereIn('status', $statuses);
        }

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
     * Toggle the status of a service between Active and Inactive.
     */
    public function toggleStatus(Service $service)
    {
        if ($service->organization_id !== Auth::id()) {
            abort(403);
        }

        $service->status = $service->status === 'A' ? 'I' : 'A';
        $service->save();

        // --- THIS IS THE FIX: Updated wording ---
        $message = $service->status === 'A' ? 'Service has been activated.' : 'Service has been made inactive.';

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