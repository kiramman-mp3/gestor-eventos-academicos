<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('registro', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('olvide-contrasena', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('olvide-contrasena', [PasswordResetController::class, 'sendLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('restablecer-contrasena/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('restablecer-contrasena', [PasswordResetController::class, 'reset'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verificar-correo', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('verificar-correo/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('verificar-correo/reenviar', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
