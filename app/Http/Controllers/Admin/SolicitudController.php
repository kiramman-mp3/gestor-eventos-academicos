<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoSolicitud;
use App\Enums\Prioridad;
use App\Http\Controllers\Controller;
use App\Models\SolicitudCambio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Revisión y resolución de solicitudes de cambio. */
class SolicitudController extends Controller
{
    public function index(Request $request): View
    {
        $estado = EstadoSolicitud::tryFrom((string) $request->query('estado'));

        $solicitudes = SolicitudCambio::query()
            ->with(['user', 'resolucion'])
            ->when($request->query('estado') === 'sin_resolver', fn ($q) => $q->doesntHave('resolucion'))
            ->when($estado, fn ($q) => $q->whereRelation('resolucion', 'estado', $estado))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.solicitudes.index', ['solicitudes' => $solicitudes]);
    }

    public function show(SolicitudCambio $solicitud): View
    {
        return view('admin.solicitudes.show', ['solicitud' => $solicitud->load(['user', 'resolucion.resolutor'])]);
    }

    public function resolver(Request $request, SolicitudCambio $solicitud): RedirectResponse
    {
        $datos = $request->validate([
            'prioridad' => ['required', Rule::enum(Prioridad::class)],
            'estado' => ['required', Rule::enum(EstadoSolicitud::class)],
            'comentario' => ['required', 'string', 'max:5000'],
        ]);

        $resolucion = $solicitud->resolucion()->firstOrNew();
        $resolucion->fill($datos);
        $resolucion->resolutor()->associate($request->user());
        $resolucion->save();

        return back()->with('status', 'Resolución guardada.');
    }
}
