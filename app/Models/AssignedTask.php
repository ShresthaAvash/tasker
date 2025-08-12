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
'name',
'due_date',
'status',
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
}