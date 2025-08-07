<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Job;
use App\Models\StaffDesignation; // ✅ ADDED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    /**
     * Show the form for editing the specified job.
     */
    public function edit(Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        // ✅ MODIFIED: Eager load tasks and fetch designations for the modal
        $job->load('tasks.designation');
        $designations = StaffDesignation::where('organization_id', Auth::id())->orderBy('name')->get();

        return view('Organization.jobs.edit', compact('job', 'designations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Service $service)
    {
        if ($service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['name' => 'required|string|max:255']);
        $service->jobs()->create($request->all());

        return redirect()->back()->with('success', 'Job added successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $job->update($request->all());
        
        // Redirect back to the edit page to see the changes
        return redirect()->route('jobs.edit', $job)->with('success', 'Job updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }
        
        $serviceId = $job->service_id;
        $job->delete();

        // Redirect back to the service builder page after deleting a job
        return redirect()->route('services.show', $serviceId)->with('success', 'Job deleted successfully.');
    }
}