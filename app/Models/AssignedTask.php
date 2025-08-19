<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedTask extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
        'color',
        'color_overrides',
        'duration_in_seconds',
        'timer_started_at',
        'completed_at_dates',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
        'start' => 'datetime',
        'end' => 'datetime',
        'is_recurring' => 'boolean',
        'color_overrides' => 'array',
        'timer_started_at' => 'datetime',
        'completed_at_dates' => 'array',
    ];

    public function staff()
    {
        return $this->belongsToMany(User::class, 'assigned_task_staff', 'assigned_task_id', 'user_id');
    }

    public function template()
    {
        return $this->belongsTo(Task::class, 'task_template_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * --- THIS IS THE FIX ---
     * Define the relationship to the Job model.
     */
    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /**
     * --- THIS IS THE OTHER FIX ---
     * Define the relationship to the Service model.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}