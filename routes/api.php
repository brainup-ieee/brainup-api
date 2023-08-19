<?php

// Auth Routes
include_once  'api_routes/auth.php';
use Illuminate\Support\Facades\Route;
Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello World!',
    ]);
});