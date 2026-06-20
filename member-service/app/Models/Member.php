<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['name', 'email', 'phone'];

    protected static function booted()
    {
        static::created(function ($member) {
            \App\Jobs\SendWelcomeEmailJob::dispatch($member);
        });
    }
}
