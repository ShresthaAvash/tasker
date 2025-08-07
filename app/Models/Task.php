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
        'end'
    ];

    /**
     * The attributes that should be cast to native types.
     * THIS IS THE FIX for the "format() on string" error.
     * It tells Laravel to treat 'start' and 'end' as proper date objects.
     */
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    /**
     * A task belongs to a job.
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * A task can be assigned to a staff designation.
     */
    public function designation()
    {
        return $this->belongsTo(StaffDesignation::class, 'staff_designation_id');
    }
}