<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Job;
use App\Models\User;
use App\Models\StaffDesignation; // Added this line
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
        $job = $service->jobs()->create($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Job added successfully!',
                'job' => $job->load('tasks') // Send back the new job with an empty tasks array
            ]);
        }

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

        $staffMembers = User::where('organization_id', Auth::id())
                            ->whereNotIn('type', ['S', 'O', 'C', 'i'])
                            ->orderBy('name')
                            ->get();
                            
        $designations = StaffDesignation::where('organization_id', Auth::id())->orderBy('name')->get();

        return view('Organization.jobs.edit', compact('job', 'staffMembers', 'designations'));
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Job updated successfully.',
                'job' => $job
            ]);
        }

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

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Job deleted successfully.']);
        }

        return redirect()->route('services.show', $serviceId)->with('success', 'Job deleted successfully.');
    }

    /**
     * Find all tasks for a job that have been assigned to a staff member
     * and change their status from 'not_started' to 'ongoing'.
     */
    public function assignTasks(Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $tasksUpdated = $job->tasks()
            ->where('status', 'not_started')
            ->whereNotNull('staff_id')
            ->update(['status' => 'ongoing']);

        if ($tasksUpdated > 0) {
            return redirect()->back()->with('success', "$tasksUpdated tasks have been activated and are now visible on the calendar.");
        }

        return redirect()->back()->with('info', 'No new tasks were ready to be assigned (or they were already active).');
    }
}