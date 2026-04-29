<?php
use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{id}', [MovieController::class, 'show']);
Route::post('/movies', [MovieController::class, 'store']);
Route::patch('/movies/{id}/seat', [MovieController::class, 'updateSeat']);
Route::get('/movies/{id}/tickets', [MovieController::class, 'movieTickets']);
