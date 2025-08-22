<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [ 'service_id', 'name', 'description', 'trigger_event', 'repeat_frequency' ];

    /**
     * A job belongs to a service.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * A job has many tasks.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('created_at');
    }

    /**
     * --- THIS IS THE FIX ---
     * A job can have many assigned tasks instantiated for clients.
     */
    public function assignedTasks()
    {
        return $this->hasMany(AssignedTask::class);
    }
}