<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\AssignedTask;
use App\Models\Task;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Notifications\TaskAssignedToStaff;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Store a newly created task in storage, associated with a job.
     */
    public function store(Request $request, Job $job)
    {
        if ($job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'is_recurring' => 'sometimes|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
        ]);

        $data = $request->all();
        $data['is_recurring'] = $request->has('is_recurring');
        $data['status'] = 'not_started';

        $task = $job->tasks()->create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task added successfully!',
                'task' => $task
            ]);
        }

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
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'is_recurring' => 'sometimes|boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
        ]);

        $data = $request->all();
        $data['is_recurring'] = $request->has('is_recurring');

        $task->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!',
                'task' => $task
            ]);
        }

        return redirect()->back()->with('success', 'Task updated successfully.');
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

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task deleted successfully.']);
        }

        return redirect()->back()->with('success', 'Task deleted successfully.');
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
     * Manually stop or update the status of a task instance.
     */
    public function stopTask(Request $request, Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }

        $newStatus = $request->input('status');
        $instanceDate = $request->input('instance_date');

        if ($task->is_recurring) {
            if (!$instanceDate) {
                return response()->json(['error' => 'Instance date is required for recurring tasks.'], 422);
            }

            $completedDates = (array) ($task->completed_at_dates ?? []);

            if ($newStatus === 'completed') {
                if (!in_array($instanceDate, $completedDates)) {
                    $completedDates[] = $instanceDate;
                }
            } else {
                $completedDates = array_filter($completedDates, fn($date) => $date !== $instanceDate);
            }

            $task->completed_at_dates = array_values(array_unique($completedDates));

            if ($newStatus !== 'completed') {
                 $task->status = $newStatus;
            }
        } else {
            $task->status = $newStatus;
        }

        $task->save();

        return response()->json(['success' => 'Status updated successfully!']);
    }
}