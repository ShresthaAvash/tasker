<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'organization_id',
        'client_id' // <-- ADD THIS LINE
    ];

    /**
     * Get the organization that owns the service.
     */
    public function organization()
    {
        return $this->belongsTo(User::class, 'organization_id');
    }

    /**
     * A service has many job templates.
     */
    public function jobs()
    {
        return $this->hasMany(Job::class)->orderBy('created_at');
    }

    /**
     * The clients that are assigned this service.
     */
    public function clients()
    {
        return $this->belongsToMany(User::class, 'client_service', 'service_id', 'user_id');
    }

    /**
     * Get the specific client this service belongs to (if it's a personal service).
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}