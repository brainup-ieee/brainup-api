<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClassroomController;

Route::prefix('classrooms/student')->middleware(['auth:sanctum'])->group(function () {
    // Join classroom
    Route::post('/join', [ClassroomController::class, 'join']);
    // Get classrooms
    Route::get('/get',[ClassroomController::class,'S_get']);

 });