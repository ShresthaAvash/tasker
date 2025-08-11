<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'title',
        'content',
        'note_date',
        'pinned_at',
    ];

    protected $casts = [
        'note_date' => 'date',
        'pinned_at' => 'datetime',
    ];

    public function isPinned()
    {
        return !is_null($this->pinned_at);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}