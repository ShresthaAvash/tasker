<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\AssignedTask;
use App\Models\TaskWorkingNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class TaskWorkingNoteController extends Controller
{
    public function index(AssignedTask $task)
    {
        $this->authorizeNoteAccess($task);
        $notes = $task->workingNotes()->with('author:id,name')->get();
        return response()->json($notes);
    }

    public function store(Request $request, AssignedTask $task)
    {
        $this->authorizeNoteAccess($task);
        $request->validate(['content' => 'required|string']);

        $note = $task->workingNotes()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return response()->json($note->load('author:id,name'));
    }

    public function update(Request $request, TaskWorkingNote $note)
    {
        $this->authorize('update', $note);
        $request->validate(['content' => 'required|string']);

        $note->update(['content' => $request->content]);

        return response()->json($note->load('author:id,name'));
    }

    public function destroy(TaskWorkingNote $note)
    {
        $this->authorize('delete', $note);
        $note->delete();

        return response()->json(['success' => true]);
    }

    private function authorizeNoteAccess(AssignedTask $task)
    {
        $user = Auth::user();
        // Organization owner's ID is their own user ID. Staff's organization_id points to the owner.
        $organizationId = in_array($user->type, ['O', 'A']) ? $user->id : $user->organization_id;

        if ($task->client->organization_id !== $organizationId) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}