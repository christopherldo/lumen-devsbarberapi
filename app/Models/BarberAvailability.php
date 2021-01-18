<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberAvailability extends Model
{
    use HasFactory;

    protected $hidden = [
        'id'
    ];

    public $timestamps = false;
}
