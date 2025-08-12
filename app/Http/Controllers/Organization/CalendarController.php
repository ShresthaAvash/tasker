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

            // 1. Get all normal, non-recurring tasks assigned to the user that are 'active'
            $nonRecurring = Task::whereNotNull('start')
                ->where('is_recurring', false)
                ->where('status', 'active') // <-- THIS IS THE FILTER
                ->where('staff_id', $userId)
                ->where('start', '<', $viewEnd)
                ->where(function ($query) use ($viewStart) {
                    $query->whereNull('end')->orWhere('end', '>', $viewStart);
                })
                ->get();

            foreach ($nonRecurring as $task) {
                $events[] = [
                    'title' => $task->name,
                    'start' => $task->start->toIso8601String(),
                    'end' => $task->end ? $task->end->toIso8601String() : null,
                ];
            }

            // 2. Get all recurring task templates assigned to the user that are 'active'
            $recurringTasks = Task::whereNotNull('start')
                ->where('is_recurring', true)
                ->where('status', 'active') // <-- THIS IS THE FILTER
                ->where('staff_id', $userId)
                ->get();

            // 3. Generate instances of recurring tasks that fall within the view
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
                    if ($task->recurring_frequency === 'daily') { $currentDate->addDay(); }
                    elseif ($task->recurring_frequency === 'weekly') { $currentDate->addWeek(); }
                    elseif ($task->recurring_frequency === 'monthly') { $currentDate->addMonth(); }
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
                  'name'  => $request->title,
                  'start' => $request->start,
                  'end'   => $request->end,
                  'staff_id' => Auth::id(),
                  'status' => 'active', // Tasks created directly on calendar are instantly active
              ]);
 
              return response()->json([
                'id'    => $event->id,
                'title' => $event->name,
                'start' => $event->start->toDateTimeString(),
                'end'   => $event->end->toDateTimeString()
              ]);
  
           case 'update':
              $event = Task::find($request->id)->update([
                  'name'  => $request->title,
                  'start' => $request->start,
                  'end'   => $request->end,
              ]);
 
              return response()->json($event);
             break;
  
           case 'delete':
              $event = Task::find($request->id)->delete();
  
              return response()->json($event);
             break;
        }
    }
}