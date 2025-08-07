<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffDesignation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'organization_id',
    ];

    /**
     * Get the organization that owns the designation.
     */
    public function organization()
    {
        return $this->belongsTo(User::class, 'organization_id');
    }
}