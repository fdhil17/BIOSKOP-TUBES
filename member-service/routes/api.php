<?php
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/members', [MemberController::class, 'index']);
Route::get('/members/{id}', [MemberController::class, 'show']);
Route::post('/members', [MemberController::class, 'store']);
Route::put('/members/{id}', [MemberController::class, 'update']);
Route::get('/members/{id}/tickets', [MemberController::class, 'memberTickets']);
