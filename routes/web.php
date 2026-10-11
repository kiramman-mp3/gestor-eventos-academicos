<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerfilController;
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

    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::patch('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password', [PerfilController::class, 'actualizarPassword'])->name('perfil.password');

    // Documentos personales (disco privado, acceso controlado por DocumentoPolicy)
    Route::post('/documentos', [DocumentoController::class, 'store'])->name('documentos.store');
    Route::get('/documentos/{documento}', [DocumentoController::class, 'show'])->name('documentos.show');
    Route::delete('/documentos/{documento}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');
});

require __DIR__.'/auth.php';
