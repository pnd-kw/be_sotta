<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CloudinaryController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\UserController;

// Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('check.role:superadmin');

    // Route untuk register
    Route::post('/register', [AuthController::class, 'register']);

    // Route untuk logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route untuk update data user berdasarkan id_user
    Route::patch('user/{id_user}', [UserController::class, 'update']);

    // Route untuk delete user berdasarkan id_user
    Route::delete('user/{id_user}', [UserController::class, 'delete']);

    // Route untuk get usage cloudinary
    Route::get('/cloudinary/usage', [CloudinaryController::class, 'getCloudinaryUsage']);

    // Route untuk get data semua user
    Route::get('/users', [UserController::class, 'index']);

    // Route untuk get data user by id_user
    Route::get('/user/{id_user}', [UserController::class, 'show']);

    // Hanya superadmin bisa delete review
    Route::delete('/customer-review/{id}', [CustomerReviewController::class, 'destroy'])
        ->middleware('check.role:superadmin');

    // Route post, update, dan delete gallery hanya bisa superadmin dan admin
    Route::middleware('check.role:admin,superadmin')->group(function () {
        Route::post('/gallery', [GalleryController::class, 'store']);
        Route::patch('/gallery/{id}', [GalleryController::class, 'update']);
        Route::patch('/gallery/{id}/published', [GalleryController::class, 'updatePublished']);
        Route::delete('/gallery/{id}', [GalleryController::class, 'delete']);
    });
});

// Route post customer review
Route::post('/customer-reviews', [CustomerReviewController::class, 'store']);

// Route get all gallery
Route::get('/gallery', [GalleryController::class, 'index']);

// Route get gallery by id
Route::get('/gallery/{id}', [GalleryController::class, 'show']);
