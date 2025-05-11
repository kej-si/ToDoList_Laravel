<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KejsiController;
use App\Http\Controllers\TaskController;

Route::post('auth/register', [KejsiController::class, 'register']);
Route::post('auth/login', [KejsiController::class, 'login']);
Route::middleware(['auth.jwt'])->get('user',[KejsiController::class, 'user']);
Route::middleware(['auth.jwt'])->post('auth/2fa/enable', [KejsiController::class, 'enable2FA']);
Route::middleware(['auth.jwt'])->post('auth/2fa/verify', [KejsiController::class, 'verify2FASetup']);

//reset password route
Route::post('auth/reset-password', [KejsiController::class, 'resetPassword']);

//task controlelr
Route::middleware(['auth.jwt'])->group(function () {
    Route::get('tasks/with-attributes', [TaskController::class, 'getTaskWithAttributes']);
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/attributes', [TaskController::class, 'addAttribute']);
    Route::get('tasks/{task}/attributes', [TaskController::class, 'getTaskAttributes']);
});