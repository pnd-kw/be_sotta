<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Route untuk register
    Route::post('/register', [AuthController::class, 'register']);
    
    // Route untuk logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route untuk update data user berdasarkan id_user
    Route::patch('user/{id_user}', [UserController::class, 'update']);

    // Route untuk delete user berdasarkan id_user
    Route::delete('user/{id_user}', [UserController::class, 'delete']);

    // Route untuk get data semua user
    Route::get('/users', [UserController::class, 'index']);

    // Route untuk get data user by id_user
    Route::get('/user/{id_user}', [UserController::class, 'show']);
});