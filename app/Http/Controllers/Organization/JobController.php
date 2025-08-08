<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    /**
     * Store a newly created job in storage.
     */
    public function store(Request $request, Service $service)
    {
        if ($service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['name' => 'required|string|max:255']);
        $service->jobs()->create($request->all());

        return redirect()->route('services.show', $service->id)->with('success', 'Job added successfully.');
    }

    /**
     * Show the form for editing the specified job.
     */
    public function edit(Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $job->load('service', 'tasks.designation', 'tasks.staff');

        // --- THIS IS THE FINAL FIX ---
        // Instead of looking for type 'M', we will look for all users in the organization
        // that are NOT the Superadmin ('S') or the Organization account ('O').
        // This is a more robust way that will include Ram ('A') and Unison ('T').
        $staffMembers = User::where('organization_id', Auth::id())
                            ->whereNotIn('type', ['S', 'O', 'C', 'i']) // Exclude Superadmin, Organization, and Clients
                            ->orderBy('name')
                            ->get();

        return view('Organization.jobs.edit', compact('job', 'staffMembers'));
    }

    /**
     * Update the specified job in storage.
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

        return redirect()->route('jobs.edit', $job->id)->with('success', 'Job updated successfully.');
    }

    /**
     * Remove the specified job from storage.
     */
    public function destroy(Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }
        
        $serviceId = $job->service_id;
        $job->delete();

        return redirect()->route('services.show', $serviceId)->with('success', 'Job deleted successfully.');
    }
}