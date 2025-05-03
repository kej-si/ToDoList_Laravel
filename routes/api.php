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
    Route::apiResource('tasks', TaskController::class);
});