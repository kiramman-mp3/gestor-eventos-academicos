<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InscripcionController;
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

    // Comprobantes de pago (dueño o administrador, ver InscripcionPolicy)
    Route::get('/comprobantes/{comprobante}', [InscripcionController::class, 'verComprobante'])->name('comprobantes.show');

    Route::middleware('role:estudiante')->group(function () {
        Route::get('/mis-inscripciones', [InscripcionController::class, 'index'])->name('inscripciones.index');
        Route::post('/eventos/{evento}/inscripcion', [InscripcionController::class, 'store'])->name('inscripciones.store');
        Route::post('/inscripciones/{inscripcion}/comprobante', [InscripcionController::class, 'subirComprobante'])->name('inscripciones.comprobante');
    });
});

/*
|--------------------------------------------------------------------------
| Administración
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:administrador'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        Route::get('/comprobantes', [Admin\ComprobanteController::class, 'index'])->name('comprobantes.index');
        Route::post('/comprobantes/{comprobante}/aprobar', [Admin\ComprobanteController::class, 'aprobar'])->name('comprobantes.aprobar');
        Route::post('/comprobantes/{comprobante}/rechazar', [Admin\ComprobanteController::class, 'rechazar'])->name('comprobantes.rechazar');
    });

require __DIR__.'/auth.php';
