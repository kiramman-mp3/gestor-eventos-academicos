<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoInscripcion;
use App\Http\Controllers\Controller;
use App\Models\Evento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/** Reporte PDF de un evento: inscritos, estado de pago, notas, asistencia y resultado. */
class ReporteController extends Controller
{
    public function __invoke(Evento $evento): Response
    {
        $evento->load(['tipoEvento', 'categorias', 'docente']);

        $inscripciones = $evento->inscripciones()
            ->with('user')
            ->get()
            ->sortBy(fn ($i) => $i->user->apellido)
            ->each->setRelation('evento', $evento);

        $resumen = collect(EstadoInscripcion::cases())
            ->mapWithKeys(fn (EstadoInscripcion $estado) => [$estado->label() => $inscripciones->where('estado', $estado)->count()]);

        return Pdf::loadView('pdf.reporte-evento', [
            'evento' => $evento,
            'inscripciones' => $inscripciones,
            'resumen' => $resumen,
            'aprobados' => $inscripciones->filter->aptaParaCertificado()->count(),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isFontSubsettingEnabled', true)
            ->stream('reporte-'.Str::slug($evento->nombre).'.pdf');
    }
}
