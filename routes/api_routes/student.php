<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\LessonController;

// Classrooms
Route::prefix('classrooms/student')->middleware(['auth:sanctum'])->group(function () {
    // Join classroom
    Route::post('/join', [ClassroomController::class, 'join']);
    // Get classrooms
    Route::get('/get',[ClassroomController::class,'S_get']);

 });
 // Lessons
 Route::prefix('lessons/student')->middleware(['auth:sanctum'])->group(function () {
    // Get lessons
    Route::get('/get/{room_id}',[LessonController::class,'getStudentLessons']);

 });
