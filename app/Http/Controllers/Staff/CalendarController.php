<?php

namespace App\Http\Controllers\Staff;

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
        // This controller is only for staff, so we directly return the staff calendar view.
        return view('Staff.tasks.calendar');
    }

    public function fetchEvents(Request $request)
    {
        $viewStart = Carbon::parse($request->start);
        $viewEnd = Carbon::parse($request->end);
        $user = Auth::user();
        $events = [];

        // --- CORRECTED QUERIES FOR STAFF ONLY ---
        $personalTasks = Task::where('staff_id', $user->id)
            ->whereNull('service_id')->whereNotNull('start')
            ->where('status', '!=', 'completed')
            ->where('start', '<=', $viewEnd)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $viewStart))
            ->get();

        $assignedTasks = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $user->id))
            ->whereNotNull('start')
            ->where('status', '!=', 'completed')
            ->where('start', '<=', $viewEnd)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $viewStart))
            ->with('client', 'service')->get();
        // --- END OF QUERIES ---
        
        // Handle non-recurring personal tasks
        foreach ($personalTasks as $task) {
            $events[] = $this->formatEvent($task, 'p', $task->start);
        }

        // Handle assigned tasks (both recurring and non-recurring)
        foreach ($assignedTasks as $task) {
            $typePrefix = 'a';
            $service = $task->service;

            if ($service && $service->is_recurring && $task->start && $service->recurring_frequency) {
                $cursor = $task->start->copy();
                $seriesEndDate = $task->end;
                $instanceData = (array) ($task->completed_at_dates ?? []);

                // Move cursor to the start of the current view window
                while($cursor->lt($viewStart)) {
                    switch ($service->recurring_frequency) {
                        case 'daily': $cursor->addDay(); break;
                        case 'weekly': $cursor->addWeek(); break;
                        case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                        case 'yearly': $cursor->addYearWithNoOverflow(); break;
                        default: break 2;
                    }
                }
                
                // Generate events within the view window
                while ($cursor->lte($viewEnd)) {
                    if ($seriesEndDate && $cursor->gt($seriesEndDate)) {
                        break;
                    }
                    
                    $instanceDateString = $cursor->toDateString();
                    $instanceSpecifics = $instanceData[$instanceDateString] ?? [];
                    $instanceStatus = $instanceSpecifics['status'] ?? $task->status;

                    if ($instanceStatus !== 'completed') {
                        $singleEvent = clone $task;
                        $singleEvent->start = $cursor->copy();
                        $singleEvent->end = $cursor->copy()->endOfDay(); // Make it an all-day event for the instance date
                        $singleEvent->status = $instanceStatus;
                        $events[] = $this->formatEvent($singleEvent, $typePrefix, $cursor);
                    }

                    switch ($service->recurring_frequency) {
                        case 'daily': $cursor->addDay(); break;
                        case 'weekly': $cursor->addWeek(); break;
                        case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                        case 'yearly': $cursor->addYearWithNoOverflow(); break;
                        default: break 2;
                    }
                }
            } elseif ($task->start && $task->status !== 'completed') {
                // This is a non-recurring assigned task
                $events[] = $this->formatEvent($task, $typePrefix, $task->start);
            }
        }

        return response()->json($events);
    }

    public function ajax(Request $request)
    {
        $user = Auth::user();

        switch ($request->type) {
            case 'update':
                $idParts = explode('_', $request->id);
                $modelType = $idParts[0] ?? null;
                $id = $idParts[1] ?? null;

                if (!$id) return response()->json(['error' => 'Invalid event ID.'], 400);
                
                $event = ($modelType === 'a') ? AssignedTask::find($id) : Task::find($id);
                if (!$event) abort(404);

                $this->authorizeStaffAction($user, $event);
                
                if ($request->has('color')) {
                    $validator = Validator::make($request->all(), ['color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/']);
                    if ($validator->fails()) return response()->json(['error' => $validator->errors()->first()], 400);

                    $overrides = $event->color_overrides ?? [];
                    $overrides[Auth::id()] = $request->color;
                    $event->update(['color_overrides' => $overrides]);

                } else {
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
        }

        return response()->json(['error' => 'Invalid action type specified.'], 400);
    }

    private function formatEvent($task, $typePrefix, Carbon $instanceDate)
    {
        $isRecurring = ($task instanceof AssignedTask && optional($task->service)->is_recurring);
        $title = ($typePrefix === 'a' && $task->client) ? $task->client->name . ': ' . $task->name : $task->name;
        
        $serviceName = 'Personal Task';
        if ($typePrefix === 'a') {
            $serviceName = optional($task->service)->name ?? 'Service Not Found';
        }

        $userId = Auth::id();
        $userColor = $task->color_overrides[$userId] ?? null;

        $backgroundColor = $userColor;

        if (!$backgroundColor) {
            $backgroundColor = $task->color;
        }

        if (!$backgroundColor) {
            if ($isRecurring) {
                $backgroundColor = '#17a2b8'; // Default Teal for recurring
            } else {
                $backgroundColor = '#fd7e14'; // Default Orange for non-recurring
            }
        }
        
        $uniqueId = $typePrefix . '_' . $task->id;
        if ($isRecurring) {
            $uniqueId .= '_' . $instanceDate->toDateString();
        }

        return [
            'id'              => $uniqueId,
            'title'           => $title,
            'start'           => $instanceDate->toIso8601String(),
            'end'             => $task->end ? $instanceDate->copy()->endOfDay()->toIso8601String() : null,
            'backgroundColor' => $backgroundColor,
            'borderColor'     => $backgroundColor,
            'textColor'       => '#FFFFFF',
            'allDay'          => true,
            'display'         => 'auto',
            'extendedProps'   => [
                'type'        => $typePrefix,
                'isRecurring' => $isRecurring,
                'actualStart' => $task->start->toIso8601String(),
                'actualEnd'   => $task->end ? $task->end->toIso8601String() : null,
                'serviceName' => $serviceName,
            ]
        ];
    }

    private function authorizeStaffAction($user, $event)
    {
        $isAuthorized = ($event instanceof Task && $event->staff_id === $user->id) || 
                        ($event instanceof AssignedTask && $event->staff()->where('users.id', $user->id)->exists());
        
        if (!$isAuthorized) {
            abort(403, 'This action is unauthorized.');
        }
    }
}