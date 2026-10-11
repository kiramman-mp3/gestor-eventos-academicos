<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoComprobante;
use App\Enums\EstadoEvento;
use App\Enums\RolUsuario;
use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\SolicitudCambio;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'Eventos abiertos' => Evento::query()->where('estado', EstadoEvento::Abierto)->count(),
                'Eventos totales' => Evento::query()->count(),
                'Estudiantes' => User::query()->where('rol', RolUsuario::Estudiante)->count(),
                'Inscripciones' => Inscripcion::query()->count(),
            ],
            'comprobantesPendientes' => Comprobante::query()->where('estado', EstadoComprobante::Pendiente)->count(),
            'solicitudesSinResolver' => SolicitudCambio::query()->doesntHave('resolucion')->count(),
            'proximos' => Evento::query()
                ->whereIn('estado', [EstadoEvento::Abierto, EstadoEvento::EnEjecucion])
                ->conCuposOcupados()
                ->orderBy('fecha_inicio')
                ->limit(5)
                ->get(),
        ]);
    }
}
