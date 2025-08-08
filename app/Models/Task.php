<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id', 'name', 'description', 'deadline_offset', 'deadline_unit', 
        'staff_designation_id', 'start', 'end',
        // --- ADDED FIELDS ---
        'is_recurring', 'recurring_frequency', 'staff_id'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'is_recurring' => 'boolean', // Automatically handle true/false
    ];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function designation()
    {
        return $this->belongsTo(StaffDesignation::class, 'staff_designation_id');
    }

    /**
     * --- ADDED RELATIONSHIP ---
     * A task can be assigned to a specific staff member (user).
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}