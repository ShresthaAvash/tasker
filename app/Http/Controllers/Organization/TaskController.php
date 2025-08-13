<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'required_if:is_recurring,true|nullable|date|after_or_equal:start',
            'is_recurring' => 'sometimes|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
            'staff_designation_id' => ['nullable', 'integer', Rule::exists('staff_designations', 'id')->where('organization_id', Auth::id())],
        ]);

        $data = $request->all();
        // Ensure 'is_recurring' is set correctly based on checkbox presence
        $data['is_recurring'] = $request->has('is_recurring');

        $job->tasks()->create($data);
        return redirect()->back()->with('success', 'Task added successfully.');
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'required_if:is_recurring,true|nullable|date|after_or_equal:start',
            'is_recurring' => 'sometimes|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
            'staff_designation_id' => ['nullable', 'integer', Rule::exists('staff_designations', 'id')->where('organization_id', Auth::id())],
        ]);

        $data = $request->all();
        // Ensure 'is_recurring' is set correctly based on checkbox presence
        $data['is_recurring'] = $request->has('is_recurring');

        $task->update($data);
        return redirect()->back()->with('success', 'Task updated successfully.');
    }
    
    /**
     * Handle the quick assignment of a staff member to a task via AJAX.
     */
    public function assignStaff(Request $request, Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('organization_id', Auth::id())],
        ]);

        $task->update(['staff_id' => $request->staff_id]);

        return response()->json(['success' => 'Task assigned successfully.']);
    }

    /**
     * Manually stop a task by setting its status to 'inactive'.
     */
    public function stopTask(Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $task->update(['status' => 'inactive']);

        return redirect()->back()->with('success', 'Task has been stopped.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted successfully.');
    }
}