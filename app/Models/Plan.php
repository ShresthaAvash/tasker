<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    // This model correctly uses the 'plans' table.
    protected $table = 'plans';

    protected $fillable = [
        'name',
        'description',
        'price',
        'type',
        'stripe_price_id',
    ];
}