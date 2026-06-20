<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = ['title', 'genre', 'duration', 'jam_tayang', 'seat_available', 'price'];

    protected static function booted()
    {
        static::created(function ($movie) {
            \App\Jobs\ProcessMovieAddedJob::dispatch($movie);
        });
    }
}
