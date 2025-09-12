<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskWorkingNote extends Model
{
    use HasFactory;

    protected $fillable = ['assigned_task_id', 'user_id', 'content'];

    /**
     * Get the user who authored the note.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the task that the note belongs to.
     */
    public function assignedTask()
    {
        return $this->belongsTo(AssignedTask::class);
    }
}