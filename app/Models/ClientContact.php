<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // ✅ UPDATED: Added email and phone to the fillable array
    protected $fillable = ['client_id', 'name', 'email', 'phone', 'position'];

    /**
     * Get the client that owns the contact.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}