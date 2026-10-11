<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Público
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');
Route::view('/creditos', 'creditos')->name('creditos');

Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

/*
|--------------------------------------------------------------------------
| Usuarios autenticados y verificados
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Punto de entrada tras iniciar sesión: redirige según el rol
    Route::get('/inicio', DashboardController::class)->name('dashboard');
});

require __DIR__.'/auth.php';
