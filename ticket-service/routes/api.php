<?php
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/tickets', [TicketController::class, 'index']);
Route::post('/tickets', [TicketController::class, 'store']);
Route::patch('/tickets/{id}/cancel', [TicketController::class, 'cancel']);
Route::get('/tickets/member/{memberId}', [TicketController::class, 'byMember']);
Route::get('/tickets/movie/{movieId}', [TicketController::class, 'byMovie']);
