<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'job_id', 
        'name', 
        'description', 
        'deadline_offset', 
        'deadline_unit', 
        'staff_designation_id', 
        'start', 
        'end',
        'is_recurring', 
        'recurring_frequency', 
        'staff_id',
        'status', // Assuming you added this from a previous step
        'color',
        // --- ADD THESE TWO LINES ---
        'duration_in_seconds',
        'timer_started_at',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'is_recurring' => 'boolean',
        // --- ADD THIS LINE ---
        'timer_started_at' => 'datetime',
    ];

    /**
     * A task belongs to a job.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * A task can be assigned to a staff designation (role).
     */
    public function designation()
    {
        return $this->belongsTo(StaffDesignation::class, 'staff_designation_id');
    }

    /**
     * A task can be assigned to a specific staff member (user).
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}