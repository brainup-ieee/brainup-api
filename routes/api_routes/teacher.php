<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\LessonController;

Route::prefix('classrooms/teacher')->middleware(['auth:sanctum'])->group(function () {
    // Create classroom
    Route::post('/create', [ClassroomController::class, 'create']);
    // Get classrooms
    Route::get('/get', [ClassroomController::class, 'get']);
    // Get classroom
    Route::get('/get/{id}', [ClassroomController::class, 'getById']);
    // Delete classroom
    Route::delete('/delete/{id}', [ClassroomController::class, 'delete']);
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
 // Lessons
   Route::prefix('lessons/teacher')->middleware(['auth:sanctum'])->group(function(){
      // Create lesson
      Route::post('/create', [LessonController::class, 'create']);
      // Get lessons
      Route::get('/get/{room_id}', [LessonController::class, 'get']);
      // Delete lesson
      Route::delete('/delete/{lesson_id}', [LessonController::class, 'delete']);
   });