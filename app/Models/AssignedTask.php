<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'service_id',
        'job_id',
        'task_template_id',
        'name',
        'description',
        'status',
        'due_date',
    ];

    /**
     * The staff members assigned to this task.
     */
    public function staff()
    {
        return $this->belongsToMany(User::class, 'assigned_task_staff', 'assigned_task_id', 'user_id');
    }
}