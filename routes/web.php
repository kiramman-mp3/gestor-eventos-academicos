<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SolicitudCambioController;
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
Route::get('/certificados/verificar/{codigo}', [CertificadoController::class, 'verificar'])
    ->middleware('throttle:30,1')
    ->name('certificados.verificar');

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

    // Certificados (CertificadoPolicy: dueño o admin, pago aprobado, requisitos cumplidos, evento finalizado)
    Route::get('/inscripciones/{inscripcion}/certificado', [CertificadoController::class, 'descargar'])->name('certificados.descargar');

    // Comprobantes de pago (dueño o administrador, ver InscripcionPolicy)
    Route::get('/comprobantes/{comprobante}', [InscripcionController::class, 'verComprobante'])->name('comprobantes.show');

    // Solicitudes de cambio (cualquier usuario autenticado)
    Route::get('/solicitudes', [SolicitudCambioController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/nueva', [SolicitudCambioController::class, 'create'])->name('solicitudes.create');
    Route::post('/solicitudes', [SolicitudCambioController::class, 'store'])->middleware('throttle:10,1')->name('solicitudes.store');
    Route::get('/solicitudes/{solicitud}/captura', [SolicitudCambioController::class, 'captura'])->name('solicitudes.captura');

    Route::middleware('role:estudiante')->group(function () {
        Route::get('/mis-inscripciones', [InscripcionController::class, 'index'])->name('inscripciones.index');
        Route::post('/eventos/{evento}/inscripcion', [InscripcionController::class, 'store'])->name('inscripciones.store');
        Route::post('/inscripciones/{inscripcion}/comprobante', [InscripcionController::class, 'subirComprobante'])->name('inscripciones.comprobante');
    });
});

/*
|--------------------------------------------------------------------------
| Calificaciones: administradores y docentes (EventoPolicy::calificar)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:administrador,docente'])
    ->prefix('gestion')
    ->name('gestion.')
    ->group(function () {
        Route::get('/calificaciones', [Admin\NotaController::class, 'index'])->name('notas.index');
        Route::get('/eventos/{evento}/notas', [Admin\NotaController::class, 'edit'])->name('notas.edit');
        Route::put('/eventos/{evento}/notas', [Admin\NotaController::class, 'update'])->name('notas.update');
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

        Route::resource('eventos', Admin\EventoAdminController::class);
        Route::patch('/eventos/{evento}/estado', [Admin\EventoAdminController::class, 'cambiarEstado'])->name('eventos.estado');
        Route::get('/eventos/{evento}/reporte', Admin\ReporteController::class)->name('eventos.reporte');

        Route::resource('usuarios', Admin\UsuarioController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::patch('/usuarios/{usuario}/rol', [Admin\UsuarioController::class, 'cambiarRol'])->name('usuarios.rol');
        Route::patch('/usuarios/{usuario}/activo', [Admin\UsuarioController::class, 'alternarActivo'])->name('usuarios.activo');

        Route::resource('categorias', Admin\CategoriaController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('tipos-evento', Admin\TipoEventoController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['tipos-evento' => 'tipoEvento']);

        Route::resource('contenidos', Admin\ContenidoController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/configuracion', [Admin\ConfiguracionController::class, 'edit'])->name('configuracion.edit');
        Route::put('/configuracion', [Admin\ConfiguracionController::class, 'update'])->name('configuracion.update');
        Route::get('/configuracion/exportar', [Admin\ConfiguracionController::class, 'exportar'])->name('configuracion.exportar');
        Route::post('/configuracion/limpiar-cache', [Admin\ConfiguracionController::class, 'limpiarCache'])->name('configuracion.limpiar-cache');

        Route::get('/solicitudes', [Admin\SolicitudController::class, 'index'])->name('solicitudes.index');
        Route::get('/solicitudes/{solicitud}', [Admin\SolicitudController::class, 'show'])->name('solicitudes.show');
        Route::put('/solicitudes/{solicitud}/resolucion', [Admin\SolicitudController::class, 'resolver'])->name('solicitudes.resolver');

        Route::get('/comprobantes', [Admin\ComprobanteController::class, 'index'])->name('comprobantes.index');
        Route::post('/comprobantes/{comprobante}/aprobar', [Admin\ComprobanteController::class, 'aprobar'])->name('comprobantes.aprobar');
        Route::post('/comprobantes/{comprobante}/rechazar', [Admin\ComprobanteController::class, 'rechazar'])->name('comprobantes.rechazar');
    });

require __DIR__.'/auth.php';
