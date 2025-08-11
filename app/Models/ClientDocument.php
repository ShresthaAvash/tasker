<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'uploaded_by_id',
        'name',
        'file_path',
        'file_type',
        'file_size',
    ];

    /**
     * Get the client that this document belongs to.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}