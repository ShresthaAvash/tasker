<?php

namespace App\Http/Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Task;
use App\Models\User;
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
            // --- THIS IS THE FIX ---
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
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
            // --- THIS IS THE FIX ---
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
            'staff_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('organization_id', Auth::id())],
        ]);

        $data = $request->all();
        $data['is_recurring'] = $request->has('is_recurring');

        $task->update($data);
        return redirect()->back()->with('success', 'Task updated successfully.');
    }
    
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

    public function stopTask(Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }
        $task->update(['status' => 'inactive']);
        return redirect()->back()->with('success', 'Task has been stopped.');
    }

    public function destroy(Task $task)
    {
        if ($task->job->service->organization_id !== Auth::id()) {
            abort(403);
        }
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted successfully.');
    }
}```

---

### **3. The `CalendarController.php` File**

Finally, we'll teach the calendar how to generate yearly recurring events.

**File to Edit:** `app/Http/Controllers/Organization/CalendarController.php`

```php
<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $viewStart = Carbon::parse($request->start);
            $viewEnd = Carbon::parse($request->end);
            $events = [];
            $userId = Auth::id();

            // Get non-recurring tasks
            $nonRecurring = Task::whereNotNull('start')->where('is_recurring', false)
                ->where('status', 'active')->where('staff_id', $userId)
                ->where('start', '<', $viewEnd)->where(fn($q) => $q->whereNull('end')->orWhere('end', '>', $viewStart))
                ->get();

            foreach ($nonRecurring as $task) {
                $events[] = [
                    'title' => $task->name,
                    'start' => $task->start->toIso8601String(),
                    'end' => $task->end ? $task->end->toIso8601String() : null,
                ];
            }

            // Get recurring task templates
            $recurringTasks = Task::whereNotNull('start')->where('is_recurring', true)
                ->where('status', 'active')->where('staff_id', $userId)->get();

            // Generate instances of recurring tasks
            foreach ($recurringTasks as $task) {
                $currentDate = $task->start->copy();
                while ($currentDate->lte($viewEnd)) {
                    if ($currentDate->gte($viewStart)) {
                        $eventEnd = null;
                        if ($task->end) {
                            $durationInSeconds = $task->start->diffInSeconds($task->end);
                            $eventEnd = $currentDate->copy()->addSeconds($durationInSeconds)->toIso8601String();
                        }
                        $events[] = [
                            'title' => $task->name,
                            'start' => $currentDate->toIso8601String(),
                            'end' => $eventEnd,
                        ];
                    }
                    // --- THIS IS THE FIX ---
                    if ($task->recurring_frequency === 'daily') { $currentDate->addDay(); }
                    elseif ($task->recurring_frequency === 'weekly') { $currentDate->addWeek(); }
                    elseif ($task->recurring_frequency === 'monthly') { $currentDate->addMonth(); }
                    elseif ($task->recurring_frequency === 'yearly') { $currentDate->addYear(); } // <-- ADD THIS LINE
                    else { break; }
                }
            }
            return response()->json($events);
        }
        return view('Organization.calendar');
    }

    public function ajax(Request $request)
    {
        switch ($request->type) {
           case 'add':
              $event = Task::create([
                  'name'  => $request->title, 'start' => $request->start, 'end' => $request->end,
                  'staff_id' => Auth::id(), 'status' => 'active',
              ]);
              return response()->json([
                'id' => $event->id, 'title' => $event->name,
                'start' => $event->start->toDateTimeString(), 'end' => $event->end->toDateTimeString()
              ]);
           case 'update':
              $event = Task::find($request->id)->update([
                  'name' => $request->title, 'start' => $request->start, 'end' => $request->end,
              ]);
              return response()->json($event);
           case 'delete':
              $event = Task::find($request->id)->delete();
              return response()->json($event);
        }
    }
}