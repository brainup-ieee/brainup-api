<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
//
// Auth Routes
Route::group(['prefix' => 'auth'], function () {
    //Register route
    Route::post('/register', [AuthController::class, 'register']);
    // //Login route
    Route::post('/login', [AuthController::class, 'login']);
    // //Forgot password route
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    // //Reset password route [Verify token]
    Route::get('/reset-password/{token}', [AuthController::class, 'verifyresetPassword']);
    Route::post('/reset-password/verify-code', [AuthController::class, 'verifyresetPasswordCode']);
    // //Reset password route [Update password]
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    // //Confirm email route
    Route::post('/confirm-email', [AuthController::class, 'confirmEmail']);
    Route::get('/confirm-email/{token}', [AuthController::class, 'verifyConfirmEmail']);
    Route::post('/confirm-email/verify-code', [AuthController::class, 'verifyConfirmEmailCode']);
});