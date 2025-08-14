<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

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

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function designation()
    {
        return $this->belongsTo(StaffDesignation::class, 'staff_designation_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}