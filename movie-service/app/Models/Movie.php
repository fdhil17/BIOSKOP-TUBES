<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'genre',
        'duration',
        'jam_tayang',
        'seat_available',
        'price'
    ];
}
