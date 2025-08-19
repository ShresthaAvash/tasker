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
    public function index()
    {
        if (Auth::user()->type === 'T') {
            return view('Organization.staff.calendar');
        }
        return view('Organization.calendar');
    }

    public function fetchEvents(Request $request)
    {
        // ... (this part is unchanged) ...
        $viewStart = Carbon::parse($request->start);
        $viewEnd = Carbon::parse($request->end);
        $user = Auth::user();
        $events = [];

        $personalTasks = Task::where('staff_id', $user->id)
            ->whereNull('job_id')->whereNotNull('start')->where('status', '!=', 'completed')
            ->where('start', '<=', $viewEnd)->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $viewStart))
            ->get();

        $assignedTaskQuery = AssignedTask::query();
        if ($user->type === 'O') {
            $assignedTaskQuery->whereHas('client', fn($q) => $q->where('organization_id', $user->id));
        } else {
            $assignedTaskQuery->whereHas('staff', fn($q) => $q->where('users.id', $user->id));
        }

        $assignedTasks = $assignedTaskQuery->whereNotNull('start')->where('status', '!=', 'completed')
            ->where('start', '<=', $viewEnd)->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $viewStart))
            ->with('client')->get();

        $allTasks = $personalTasks->concat($assignedTasks);

        foreach ($allTasks as $task) {
            $typePrefix = $task instanceof AssignedTask ? 'assigned' : 'personal';
            if ($task->is_recurring && $task->end && $task->recurring_frequency) {
                $cursor = $task->start->copy();
                $seriesEndDate = $task->end;
                while ($cursor->lte($seriesEndDate)) {
                    if ($cursor->between($viewStart, $viewEnd)) {
                        $singleEvent = clone $task;
                        $singleEvent->start = $cursor->copy();
                        $singleEvent->end = $cursor->copy()->endOfDay();
                        $events[] = $this->formatEvent($singleEvent, $typePrefix);
                    }
                    if ($cursor > $viewEnd) break;
                    switch ($task->recurring_frequency) {
                        case 'daily': $cursor->addDay(); break;
                        case 'weekly': $cursor->addWeek(); break;
                        case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                        case 'yearly': $cursor->addYearWithNoOverflow(); break;
                        default: break 2;
                    }
                }
            } else {
                $events[] = $this->formatEvent($task, $typePrefix);
            }
        }
        return response()->json($events);
    }
    
    // MODIFIED: The 'update' case now saves to the new JSON column
    public function ajax(Request $request)
    {
        $user = Auth::user();

        switch ($request->type) {
            // ... ('add' case is unchanged) ...
            case 'add':
                $validator = Validator::make($request->all(), [
                    'title' => 'required|string|max:255',
                    'start' => 'required|date',
                    'end'   => 'nullable|date|after_or_equal:start',
                ]);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->first()], 400);
                }
                $event = Task::create([ 'name' => $request->title, 'start' => $request->start, 'end' => $request->end, 'staff_id'  => $user->id, 'status' => 'active' ]);
                return response()->json($this->formatEvent($event, 'personal'));

            case 'update':
                $idParts = explode('_', $request->id);
                $modelType = $idParts[0] ?? null;
                $id = $idParts[1] ?? null;

                if (!$id) return response()->json(['error' => 'Invalid event ID.'], 400);
                
                $event = ($modelType === 'assigned') ? AssignedTask::find($id) : Task::find($id);
                if (!$event) abort(404);

                $this->authorizeCalendarAction($user, $event);
                
                // --- THIS IS THE KEY CHANGE FOR SAVING COLORS ---
                if ($request->has('color')) {
                    $validator = Validator::make($request->all(), ['color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/']);
                    if ($validator->fails()) return response()->json(['error' => $validator->errors()->first()], 400);

                    // Get existing overrides or initialize an empty array
                    $overrides = $event->color_overrides ?? [];
                    // Set the color for the current user
                    $overrides[Auth::id()] = $request->color;
                    // Save the updated overrides
                    $event->update(['color_overrides' => $overrides]);

                } else {
                    // This handles drag-and-drop date updates
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
                    $validator = Validator::make($request->all(), $validatorRules);
                    if ($validator->fails()) return response()->json(['error' => $validator->errors()->first()], 400);
                    $event->update($updateData);
                }
                
                return response()->json(['status' => 'success', 'message' => 'Event updated successfully.']);

            // ... ('delete' case is unchanged) ...
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
     * --- MODIFIED METHOD ---
     * Formats a task object into a FullCalendar event object.
     * It now checks for a user-specific color override before falling back to other colors.
     */
    private function formatEvent($task, $typePrefix)
    {
        $isRecurring = (bool) $task->is_recurring;
        $title = ($typePrefix === 'assigned' && $task->client) ? $task->client->name . ': ' . $task->name : $task->name;
        
        // --- THIS IS THE KEY CHANGE FOR DISPLAYING COLORS ---
        $userId = Auth::id();
        $userColor = $task->color_overrides[$userId] ?? null;

        // 1. Use user-specific override color if it exists
        $backgroundColor = $userColor;

        // 2. If no user color, use the global task color
        if (!$backgroundColor) {
            $backgroundColor = $task->color;
        }

        // 3. If still no color, use defaults based on recurrence
        if (!$backgroundColor) {
            if ($isRecurring) {
                $backgroundColor = '#17a2b8'; // Default Teal for recurring
            } else {
                $backgroundColor = '#fd7e14'; // Default Orange for non-recurring
            }
        }

        return [
            'id'              => $typePrefix . '_' . $task->id,
            'title'           => $title,
            'start'           => $task->start->toIso8601String(),
            'end'             => $task->end ? $task->end->toIso8601String() : null,
            'backgroundColor' => $backgroundColor,
            'borderColor'     => $backgroundColor,
            'textColor'       => '#FFFFFF',
            'allDay'          => true,
            'display'         => 'auto',
            'extendedProps'   => [
                'type'        => $typePrefix,
                'isRecurring' => $isRecurring,
                'actualStart' => $task->start->toIso8601String(),
                'actualEnd'   => $task->end ? $task->end->toIso8601String() : null
            ]
        ];
    }

    private function authorizeCalendarAction($user, $event)
    {
        $isAuthorized = false;
        if ($user->type === 'O') {
            $isAuthorized = ($event instanceof Task && optional(optional($event->job)->service)->organization_id === $user->id) || 
                            ($event instanceof AssignedTask && optional($event->client)->organization_id === $user->id);
        } elseif ($user->type === 'T') {
            $isAuthorized = ($event instanceof Task && $event->staff_id === $user->id) || 
                            ($event instanceof AssignedTask && $event->staff()->where('users.id', $user->id)->exists());
        }
        if (!$isAuthorized) {
            abort(403, 'This action is unauthorized.');
        }
    }
}