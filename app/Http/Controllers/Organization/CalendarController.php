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
        $viewStart = Carbon::parse($request->start);
        $viewEnd = Carbon::parse($request->end);
        $user = Auth::user();
        $events = [];

        // --- MODIFIED: Added where('status', '!=', 'completed') ---
        $personalTasks = Task::where('staff_id', $user->id)
            ->whereNull('job_id')->whereNotNull('start')
            ->where('status', '!=', 'completed') // This line is new
            ->where('start', '<=', $viewEnd)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $viewStart))
            ->get();

        $assignedTaskQuery = AssignedTask::query();
        if ($user->type === 'O') {
            $assignedTaskQuery->whereHas('client', fn($q) => $q->where('organization_id', $user->id));
        } else {
            $assignedTaskQuery->whereHas('staff', fn($q) => $q->where('users.id', $user->id));
        }

        // --- MODIFIED: Added where('status', '!=', 'completed') ---
        $assignedTasks = $assignedTaskQuery->whereNotNull('start')
            ->where('status', '!=', 'completed') // This line is new
            ->where('start', '<=', $viewEnd)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $viewStart))
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

    private function formatEvent($task, $typePrefix)
    {
        $isRecurring = (bool) $task->is_recurring;
        $backgroundColor = $isRecurring ? '#17a2b8' : '#3788d8';
        $textColor = $task->color ?? '#FFFFFF';
        $title = ($typePrefix === 'assigned' && $task->client) ? $task->client->name . ': ' . $task->name : $task->name;

        return [
            'id'              => $typePrefix . '_' . $task->id,
            'title'           => $title,
            'start'           => $task->start->toIso8601String(),
            'end'             => $task->end ? $task->end->toIso8601String() : null,
            'backgroundColor' => $backgroundColor,
            'borderColor'     => $backgroundColor,
            'textColor'       => $textColor,
            'allDay'          => $isRecurring,
            'display'         => $isRecurring ? 'auto' : 'block',
            'extendedProps'   => [
                'actualStart' => $task->start->toIso8601String(),
                'actualEnd' => $task->end ? $task->end->toIso8601String() : null
            ]
        ];
    }
    
    public function ajax(Request $request)
    {
        $user = Auth::user();
        $idParts = explode('_', $request->id);
        $modelType = $idParts[0] ?? null;
        $id = $idParts[1] ?? null;

        if (!$id) return response()->json(['error' => 'Invalid ID.'], 400);
        
        $event = ($modelType === 'assigned') ? AssignedTask::find($id) : Task::find($id);
        if (!$event) abort(404);

        $this->authorizeCalendarAction($user, $event);

        switch ($request->type) {
           case 'updateColor':
              $validator = Validator::make($request->all(), ['color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/']);
              if ($validator->fails()) return response()->json(['error' => 'Invalid color format.'], 400);
              $event->update(['color' => $request->color]);
              return response()->json(['status' => 'success']);

           case 'delete':
              $event->delete();
              return response()->json(['status' => 'success']);
        }

        return response()->json(['error' => 'Invalid action.'], 400);
    }
    
    private function authorizeCalendarAction($user, $event) {
        $isAuthorized = false;
        if ($user->type === 'O') {
            $isAuthorized = ($event instanceof Task && optional(optional($event->job)->service)->organization_id === $user->id) || 
                            ($event instanceof AssignedTask && optional($event->client)->organization_id === $user->id);
        } elseif ($user->type === 'T') {
            $isAuthorized = ($event instanceof Task && $event->staff_id === $user->id) || 
                            ($event instanceof AssignedTask && $event->staff()->where('user_id', $user->id)->exists());
        }
        if (!$isAuthorized) {
            abort(403, 'Unauthorized action.');
        }
    }
}