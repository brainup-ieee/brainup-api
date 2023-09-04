<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClassroomController;

Route::prefix('classrooms/teacher')->middleware(['auth:sanctum'])->group(function () {
    // Create classroom
    Route::post('/create', [ClassroomController::class, 'create']);
    // Get classrooms
    Route::get('/get', [ClassroomController::class, 'get']);
    // Get classroom
    Route::get('/get/{id}', [ClassroomController::class, 'getById']);
    // Delete classroom

    // Approve request
    Route::post('/approve', [ClassroomController::class, 'approveRequest']);
    // Reject request
    Route::post('/reject', [ClassroomController::class, 'rejectRequest']);

 });