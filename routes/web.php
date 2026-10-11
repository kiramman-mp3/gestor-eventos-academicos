<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Punto de entrada tras iniciar sesión: redirige según el rol
    Route::get('/inicio', DashboardController::class)->name('dashboard');
});

require __DIR__.'/auth.php';
