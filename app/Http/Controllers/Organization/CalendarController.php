<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Log; // Import the Log facade

class CalendarController extends Controller
{
    /**
     * Display the calendar and fetch events for the visible date range.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            
            // --- DEBUGGING: Log the incoming request from FullCalendar ---
            Log::info('--- Calendar Event Request ---');
            Log::info('Request Start Param: ' . $request->start);
            Log::info('Request End Param: ' . $request->end);

            try {
                // The query to find any task that overlaps with the calendar's visible date range.
                $tasks = Task::whereNotNull('start')
                            ->where('start', '<=', $request->end)
                            ->where(function ($query) use ($request) {
                                $query->whereNull('end')
                                      ->orWhere('end', '>=', $request->start);
                            })
                            ->get(['id', 'name', 'start', 'end']);
                
                // --- DEBUGGING: Log how many tasks were found ---
                Log::info('Found ' . $tasks->count() . ' tasks in the date range.');

                // Format the events into the array structure FullCalendar needs
                $formattedEvents = [];
                foreach ($tasks as $task) {
                    $formattedEvents[] = [
                        'id'    => $task->id,
                        'title' => $task->name,
                        'start' => $task->start->toIso8601String(), // Use a robust, universal date format
                        'end'   => $task->end ? $task->end->toIso8601String() : null,
                    ];
                }
      
                // --- DEBUGGING: Log the exact data being sent back to the browser ---
                Log::info('Returning events JSON: ' . json_encode($formattedEvents));
                
                return response()->json($formattedEvents);

            } catch (\Exception $e) {
                // If anything crashes, log the detailed error.
                Log::error('Error fetching calendar events: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                return response()->json(['error' => 'An error occurred on the server.'], 500);
            }
        }
      
        return view('Organization.calendar');
    }
 
    /**
     * Handle creating, updating, and deleting events via AJAX.
     */
    public function ajax(Request $request)
    {
        // This function is working correctly, so we leave it as is.
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
        }
    }
}