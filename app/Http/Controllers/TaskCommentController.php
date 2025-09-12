<?php

namespace App\Http\Controllers;

use App\Models\AssignedTask;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class TaskCommentController extends Controller
{
    public function index(AssignedTask $task)
    {
        $this->authorizeCommentAccess($task);
        $comments = $task->comments()->with('author:id,name,type')->get();
        return response()->json($comments);
    }

    public function store(Request $request, AssignedTask $task)
    {
        $this->authorizeCommentAccess($task);
        $request->validate(['content' => 'required|string']);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return response()->json($comment->load('author:id,name,type'));
    }

    public function update(Request $request, TaskComment $comment)
    {
        $this->authorize('update', $comment);
        $request->validate(['content' => 'required|string']);

        $comment->update(['content' => $request->content]);

        return response()->json($comment->load('author:id,name,type'));
    }

    public function destroy(TaskComment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json(['success' => true]);
    }

    private function authorizeCommentAccess(AssignedTask $task)
    {
        $user = Auth::user();

        // Allow access if the user is the client for the task
        if ($user->type === 'C' && $task->client_id === $user->id) {
            return;
        }

        // Allow access if the user belongs to the organization that owns the task
        $organizationId = in_array($user->type, ['O', 'A']) ? $user->id : $user->organization_id;
        if ($task->client->organization_id === $organizationId) {
            return;
        }

        throw new AuthorizationException('This action is unauthorized.');
    }
}