<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Task;
use App\Models\User; // Import the User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function store(Request $request, Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'is_recurring' => 'sometimes|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly',
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('organization_id', Auth::id())],
        ]);

        $data = $request->all();
        $data['is_recurring'] = $request->has('is_recurring');

        $job->tasks()->create($data);
        return redirect()->back()->with('success', 'Task added successfully.');
    }

    public function update(Request $request, Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'is_recurring' => 'sometimes|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly',
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('organization_id', Auth::id())],
        ]);

        $data = $request->all();
        $data['is_recurring'] = $request->has('is_recurring');

        $task->update($data);
        return redirect()->back()->with('success', 'Task updated successfully.');
    }
    
    /**
     * --- NEW METHOD FOR AJAX ASSIGNMENT ---
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

    public function destroy(Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted successfully.');
    }
}