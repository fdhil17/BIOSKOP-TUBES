<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    // Mengizinkan kolom ini diisi secara massal
    protected $fillable = [
        'title',
        'genre',
        'duration',
        'jam_tayang',
        'seat_available',
        'price'
    ];
}