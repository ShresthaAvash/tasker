<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = ['assigned_task_id', 'user_id', 'content'];

    /**
     * Get the user who authored the comment.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the task that the comment belongs to.
     */
    public function assignedTask()
    {
        return $this->belongsTo(AssignedTask::class);
    }
}