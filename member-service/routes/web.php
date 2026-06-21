<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => env('APP_NAME'),
        'status' => 'OK'
    ]);
});
