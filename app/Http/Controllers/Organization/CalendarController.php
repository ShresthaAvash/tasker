<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\AssignedTask; // Piyush's new model
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

            // === 1. GET ASSIGNED CLIENT TASKS (Piyush's Feature) ===
            // This feature seems to be in development, we will keep it but add your status filter.
            // Note: If you don't have an `AssignedTask` model, this part might need to be commented out.
            if (class_exists(AssignedTask::class)) {
                // Non-Recurring Assigned Tasks
                $nonRecurringAssigned = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $userId))
                    ->where('is_recurring', false)
                    ->whereNotNull('start')
                    ->where('start', '<', $viewEnd)
                    ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>', $viewStart))
                    ->with('client')
                    ->get();

                foreach ($nonRecurringAssigned as $task) {
                    $events[] = $this->formatEvent($task, 'assigned');
                }
                
                // Recurring Assigned Tasks (Templates)
                $recurringAssigned = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $userId))
                    ->where('is_recurring', true)
                    ->whereNotNull('start')
                    ->with('client')
                    ->get();
                
                foreach ($recurringAssigned as $taskTemplate) {
                    $events = array_merge($events, $this->generateRecurringEvents($taskTemplate, $viewStart, $viewEnd, 'assigned'));
                }
            }


            // === 2. GET PERSONAL STAFF TASKS (Your Feature) ===
            // This is the logic we built together, merged with Piyush's structure.

            // Non-Recurring Personal Tasks (from your code, with status filter)
            $nonRecurringPersonal = Task::where('staff_id', $userId)->whereNull('job_id')
                ->where('is_recurring', false)
                ->where('status', 'active') // <-- Your merged feature
                ->whereNotNull('start')
                ->where('start', '<', $viewEnd)
                ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>', $viewStart))
                ->get();

            foreach ($nonRecurringPersonal as $task) {
                $events[] = $this->formatEvent($task, 'personal');
            }

            // Recurring Personal Tasks (from your code, with status filter)
            $recurringPersonal = Task::where('staff_id', $userId)->whereNull('job_id')
                ->where('is_recurring', true)
                ->where('status', 'active') // <-- Your merged feature
                ->whereNotNull('start')
                ->get();

            foreach ($recurringPersonal as $taskTemplate) {
                $events = array_merge($events, $this->generateRecurringEvents($taskTemplate, $viewStart, $viewEnd, 'personal'));
            }

            return response()->json($events);
        }
        return view('Organization.calendar');
    }

    private function formatEvent($task, $typePrefix)
    {
        $title = ($typePrefix === 'assigned' && isset($task->client))
            ? $task->client->name . ': ' . $task->name
            : $task->name;

        $color = ($typePrefix === 'assigned') ? '#28a745' : '#007bff'; // Green for client tasks, Blue for personal

        return [
            'id'      => $typePrefix . '_' . $task->id,
            'title'   => $title,
            'start'   => $task->start->toIso8601String(),
            'end'     => $task->end ? $task->end->toIso8601String() : null,
            'color'   => $color,
        ];
    }

    private function generateRecurringEvents($taskTemplate, Carbon $viewStart, Carbon $viewEnd, $typePrefix)
    {
        $generatedEvents = [];
        $currentDate = $taskTemplate->start->copy();
        $durationInSeconds = $taskTemplate->end ? $taskTemplate->start->diffInSeconds($taskTemplate->end) : null;

        while ($currentDate->lte($viewEnd)) {
            if ($currentDate->gte($viewStart)) {
                $eventData = $this->formatEvent($taskTemplate, $typePrefix);
                $eventData['start'] = $currentDate->toIso8601String();
                $eventData['end'] = $durationInSeconds !== null ? $currentDate->copy()->addSeconds($durationInSeconds)->toIso8601String() : null;
                $generatedEvents[] = $eventData;
            }
            
            // Move to the next occurrence
            switch ($taskTemplate->recurring_frequency) {
                case 'daily': $currentDate->addDay(); break;
                case 'weekly': $currentDate->addWeek(); break;
                case 'monthly': $currentDate->addMonth(); break;
                case 'yearly': $currentDate->addYear(); break;
                default: break 2;
            }

            if ($taskTemplate->start > $currentDate) break;
        }
        return $generatedEvents;
    }

    public function ajax(Request $request)
    {
        $idParts = explode('_', $request->id);
        $modelType = $idParts[0] ?? 'personal'; // Default to 'personal' for new events
        $id = $idParts[1] ?? $request->id;

        switch ($request->type) {
           case 'add':
              $event = Task::create([
                  'name'      => $request->title,
                  'start'     => $request->start,
                  'end'       => $request->end,
                  'staff_id'  => Auth::id(),
                  'status'    => 'active', // Merged: New personal tasks are instantly active
              ]);
              return response()->json($this->formatEvent($event, 'personal')); // Return in the new format
  
           case 'update':
              if ($modelType === 'assigned' && $id && class_exists(AssignedTask::class)) {
                  $event = AssignedTask::find($id)->update(['start' => $request->start, 'end' => $request->end]);
              } elseif ($modelType === 'personal' && $id) {
                  $event = Task::find($id)->update(['start' => $request->start, 'end' => $request->end]);
              } else {
                  return response()->json(['error' => 'Invalid task type for update.'], 400);
              }
              return response()->json($event);
             break;
  
           case 'delete':
              if ($modelType === 'assigned' && $id && class_exists(AssignedTask::class)) {
                  $event = AssignedTask::find($id)->delete();
              } elseif ($modelType === 'personal' && $id) {
                  $event = Task::find($id)->delete();
              } else {
                 return response()->json(['error' => 'Invalid task type for deletion.'], 400);
              }
              return response()->json($event);
             break;
        }
    }
}