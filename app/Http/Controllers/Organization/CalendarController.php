<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task; // We need to import the Task model

class CalendarController extends Controller
{
    /**
     * Display the calendar view and fetch events for it.
     */
    public function index(Request $request)
    {
        // This part handles the AJAX request from the calendar when it loads.
        if($request->ajax()) {
       
             // *** THIS IS THE FINAL FIX ***
             // The old query was too strict. This new query correctly finds all tasks
             // that overlap with the calendar's visible date range.
             $tasks = Task::whereNotNull('start')
                        ->where('start', '<=', $request->end)
                        ->where('end', '>=', $request->start)
                        ->get(['id', 'name', 'start', 'end']);
            
            // Manually format the tasks into an array that FullCalendar will understand.
            $formattedEvents = [];
            foreach ($tasks as $task) {
                $formattedEvents[] = [
                    'id'    => $task->id,
                    'title' => $task->name, // Map the 'name' column to 'title'.
                    'start' => $task->start->format('Y-m-d H:i:s'), // Format dates explicitly.
                    'end'   => $task->end ? $task->end->format('Y-m-d H:i:s') : null,
                ];
            }
  
             // Return the perfectly formatted array as JSON.
             return response()->json($formattedEvents);
        }
  
        // This part just loads the blank calendar page view initially.
        return view('Organization.calendar');
    }
 
    /**
     * Handle creating, updating, and deleting events via AJAX.
     */
    public function ajax(Request $request)
    {
        switch ($request->type) {
           case 'add':
              $event = Task::create([
                  'name'  => $request->title,
                  'start' => $request->start,
                  'end'   => $request->end,
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
             
           default:
             # code...
             break;
        }
    }
}