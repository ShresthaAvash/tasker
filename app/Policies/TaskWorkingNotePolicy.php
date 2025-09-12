<?php

namespace App\Policies;

use App\Models\TaskWorkingNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskWorkingNotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaskWorkingNote  $taskWorkingNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TaskWorkingNote $taskWorkingNote)
    {
        return $user->id === $taskWorkingNote->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaskWorkingNote  $taskWorkingNote
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TaskWorkingNote $taskWorkingNote)
    {
        return $user->id === $taskWorkingNote->user_id;
    }
}