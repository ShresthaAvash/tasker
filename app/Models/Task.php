<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [ 'job_id', 'name', 'description', 'deadline_offset', 'deadline_unit', 'staff_designation_id' ];

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