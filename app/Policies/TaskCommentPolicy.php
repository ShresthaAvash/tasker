<?php

namespace App\Policies;

use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaskComment  $taskComment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TaskComment $taskComment)
    {
        return $user->id === $taskComment->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TaskComment  $taskComment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TaskComment $taskComment)
    {
        return $user->id === $taskComment->user_id;
    }
}