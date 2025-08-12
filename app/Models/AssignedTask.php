<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'task_template_id',
        'service_id',
        'job_id',
        'name',
        'description',
        'due_date',
        'status',
        'start',
        'end',
        'is_recurring',
        'recurring_frequency',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'due_date' => 'date',
        'start' => 'datetime',
        'end' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    /**
     * The staff members assigned to this task.
     */
    public function staff()
    {
        return $this->belongsToMany(User::class, 'assigned_task_staff', 'assigned_task_id', 'user_id');
    }

    /**
     * Get the original task template.
     */
    public function template()
    {
        return $this->belongsTo(Task::class, 'task_template_id');
    }

    /**
     * Get the client for this assigned task.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}