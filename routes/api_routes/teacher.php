<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\QuizController;

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
 // Quizzes
 Route::prefix('quizes/teacher')->middleware(['auth:sanctum'])->group(function(){
    // Create quiz
    Route::post('/create', [QuizController::class, 'create']);
    // Get quizzes
    Route::get('/get', [QuizController::class, 'get']);
 });