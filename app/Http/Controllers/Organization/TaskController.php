<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Task;
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
            'deadline_offset' => 'required|integer|min:0',
            'deadline_unit' => 'required|in:days,weeks,months,years',
            'staff_designation_id' => [
                'nullable', 'integer',
                Rule::exists('staff_designations', 'id')->where('organization_id', Auth::id()),
            ],
        ]);

        $job->tasks()->create($request->all());

        // ✅ MODIFIED: Redirect back to the previous page
        return redirect()->back()->with('success', 'Task added successfully.');
    }

    public function update(Request $request, Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'deadline_offset' => 'required|integer|min:0',
            'deadline_unit' => 'required|in:days,weeks,months,years',
            'staff_designation_id' => [
                'nullable', 'integer',
                Rule::exists('staff_designations', 'id')->where('organization_id', Auth::id()),
            ],
        ]);

        $task->update($request->all());

        // ✅ MODIFIED: Redirect back to the previous page
        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $task->delete();

        // ✅ MODIFIED: Redirect back to the previous page
        return redirect()->back()->with('success', 'Task deleted successfully.');
    }
}