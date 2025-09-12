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
        'service_id', 
        'name', 
        'description', 
        'deadline_offset', 
        'deadline_unit', 
        'staff_designation_id', 
        'start', 
        'end',
        'color_overrides', 
        'staff_id',
        'status',
        'color',
        'duration_in_seconds',
        'timer_started_at',
        'completed_at_dates',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
         'color_overrides' => 'array',
        'timer_started_at' => 'datetime',
        'completed_at_dates' => 'array',
    ];

    /**
     * A task belongs to a service.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
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