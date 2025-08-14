<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\AssignedTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    /**
     * Display the calendar view based on user type.
     */
    public function index()
    {
        if (Auth::user()->type === 'T') {
            return view('Organization.staff.calendar');
        }
        return view('Organization.calendar');
    }

    /**
     * Fetch events for the calendar's current view.
     * This method is called via AJAX by the calendar plugin.
     */
    public function fetchEvents(Request $request)
    {
        $viewStart = Carbon::parse($request->start);
        $viewEnd = Carbon::parse($request->end);
        $user = Auth::user();
        $events = [];

        // 1. Get Personal Tasks
        // Fetches tasks that are not completed and fall within the calendar's view.
        $personalTasks = Task::where('staff_id', $user->id)
            ->whereNull('job_id')
            ->whereNotNull('start')
            ->where('status', '!=', 'completed')
            ->where('start', '<=', $viewEnd)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $viewStart))
            ->get();

        // 2. Get Assigned Tasks (Client-related)
        // Builds a query based on whether the user is an Organization admin or Staff.
        $assignedTaskQuery = AssignedTask::query();
        if ($user->type === 'O') {
            $assignedTaskQuery->whereHas('client', fn($q) => $q->where('organization_id', $user->id));
        } else { // 'T' for Staff
            $assignedTaskQuery->whereHas('staff', fn($q) => $q->where('users.id', $user->id));
        }

        $assignedTasks = $assignedTaskQuery->whereNotNull('start')
            ->where('status', '!=', 'completed')
            ->where('start', '<=', $viewEnd)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $viewStart))
            ->with('client')->get();

        // 3. Process all tasks (Personal and Assigned)
        $allTasks = $personalTasks->concat($assignedTasks);

        foreach ($allTasks as $task) {
            $typePrefix = $task instanceof AssignedTask ? 'assigned' : 'personal';

            // If the task is recurring, generate event instances for it.
            if ($task->is_recurring && $task->end && $task->recurring_frequency) {
                $cursor = $task->start->copy();
                // Use the task's end date as the end of the series.
                $seriesEndDate = $task->end;

                while ($cursor->lte($seriesEndDate)) {
                    // Only add events that are within the calendar's current view.
                    if ($cursor->between($viewStart, $viewEnd)) {
                        $singleEvent = clone $task;
                        $singleEvent->start = $cursor->copy();
                        $singleEvent->end = $cursor->copy()->endOfDay(); // Recurring tasks are all-day by default
                        $events[] = $this->formatEvent($singleEvent, $typePrefix);
                    }
                    // Stop generating if we go past the view's end
                    if ($cursor > $viewEnd) break;

                    // Increment the cursor to the next occurrence.
                    switch ($task->recurring_frequency) {
                        case 'daily': $cursor->addDay(); break;
                        case 'weekly': $cursor->addWeek(); break;
                        case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                        case 'yearly': $cursor->addYearWithNoOverflow(); break;
                        default: break 2; // Exit the loop if frequency is invalid
                    }
                }
            } else {
                // It's a non-recurring task.
                $events[] = $this->formatEvent($task, $typePrefix);
            }
        }

        return response()->json($events);
    }

    /**
     * Handles all AJAX actions from the calendar (add, update, delete).
     */
    public function ajax(Request $request)
    {
        $user = Auth::user();

        switch ($request->type) {
            // Feature from "Incoming Code": Add a new personal task
            case 'add':
                $validator = Validator::make($request->all(), [
                    'title' => 'required|string|max:255',
                    'start' => 'required|date',
                    'end'   => 'nullable|date|after_or_equal:start',
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->first()], 400);
                }

                $event = Task::create([
                    'name'      => $request->title,
                    'start'     => $request->start,
                    'end'       => $request->end,
                    'staff_id'  => $user->id,
                    'status'    => 'active', // New tasks are active by default
                ]);
                // Return the newly created event in the correct calendar format
                return response()->json($this->formatEvent($event, 'personal'));

            // Merged Feature: Update an existing task's dates or color
            case 'update':
                $idParts = explode('_', $request->id);
                $modelType = $idParts[0] ?? null;
                $id = $idParts[1] ?? null;

                if (!$id) return response()->json(['error' => 'Invalid event ID.'], 400);
                
                $event = ($modelType === 'assigned') ? AssignedTask::find($id) : Task::find($id);
                if (!$event) abort(404);

                $this->authorizeCalendarAction($user, $event);
                
                $updateData = [];
                $validatorRules = [];

                if($request->has('start')) {
                    $updateData['start'] = $request->start;
                    $validatorRules['start'] = 'required|date';
                }
                if($request->has('end')) {
                    $updateData['end'] = $request->end;
                    $validatorRules['end'] = 'nullable|date|after_or_equal:start';
                }
                if($request->has('color')) {
                    $updateData['color'] = $request->color;
                    $validatorRules['color'] = 'required|string|regex:/^#[a-fA-F0-9]{6}$/';
                }

                $validator = Validator::make($request->all(), $validatorRules);
                if ($validator->fails()) return response()->json(['error' => $validator->errors()->first()], 400);

                $event->update($updateData);
                return response()->json(['status' => 'success', 'message' => 'Event updated successfully.']);

            // Feature from "Current Code": Delete an event
            case 'delete':
                $idParts = explode('_', $request->id);
                $modelType = $idParts[0] ?? null;
                $id = $idParts[1] ?? null;

                if (!$id) return response()->json(['error' => 'Invalid event ID.'], 400);

                $event = ($modelType === 'assigned') ? AssignedTask::find($id) : Task::find($id);
                if (!$event) abort(404);

                $this->authorizeCalendarAction($user, $event);
                $event->delete();
                return response()->json(['status' => 'success', 'message' => 'Event deleted successfully.']);
        }

        return response()->json(['error' => 'Invalid action type specified.'], 400);
    }

    /**
     * Formats a task object into a FullCalendar event object.
     * Merges styling logic from both versions.
     */
    private function formatEvent($task, $typePrefix)
    {
        $isRecurring = (bool) $task->is_recurring;
        $title = ($typePrefix === 'assigned' && $task->client) ? $task->client->name . ': ' . $task->name : $task->name;
        
        // Dynamic background color logic
        $backgroundColor = $task->color; // 1. Use custom color if it exists
        if (!$backgroundColor) {
            // 2. Fallback to type-based colors
            $backgroundColor = ($typePrefix === 'assigned') ? '#28a745' : '#007bff'; // Green for client, Blue for personal
        }
        if ($isRecurring && !$task->color) {
            // 3. Override with a specific color for recurring events if no custom color is set
            $backgroundColor = '#17a2b8'; // Teal for recurring
        }

        return [
            'id'              => $typePrefix . '_' . $task->id,
            'title'           => $title,
            'start'           => $task->start->toIso8601String(),
            'end'             => $task->end ? $task->end->toIso8601String() : null,
            'backgroundColor' => $backgroundColor,
            'borderColor'     => $backgroundColor, // Match border to background
            'textColor'       => '#FFFFFF',
            'allDay'          => $isRecurring,
            'display'         => 'auto',
            'extendedProps'   => [
                'type'        => $typePrefix,
                'actualStart' => $task->start->toIso8601String(),
                'actualEnd'   => $task->end ? $task->end->toIso8601String() : null
            ]
        ];
    }

    /**
     * Authorizes that the current user is allowed to modify an event.
     * A critical security feature from the "Current Code".
     */
    private function authorizeCalendarAction($user, $event) {
        $isAuthorized = false;
        // Organization users can manage tasks linked to their clients/services.
        if ($user->type === 'O') {
            $isAuthorized = ($event instanceof Task && optional(optional($event->job)->service)->organization_id === $user->id) || 
                            ($event instanceof AssignedTask && optional($event->client)->organization_id === $user->id);
        // Staff users can only manage tasks directly assigned to them.
        } elseif ($user->type === 'T') {
            $isAuthorized = ($event instanceof Task && $event->staff_id === $user->id) || 
                            ($event instanceof AssignedTask && $event->staff()->where('users.id', $user->id)->exists());
        }
        // If not authorized, stop the request with a "Forbidden" error.
        if (!$isAuthorized) {
            abort(403, 'This action is unauthorized.');
        }
    }
}