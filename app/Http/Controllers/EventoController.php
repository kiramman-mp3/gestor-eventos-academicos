<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\TipoEvento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Catálogo público de eventos (filtrado por destinatario según el usuario). */
class EventoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tipo' => ['nullable', 'integer'],
        ]);

        $eventos = Evento::query()
            ->visiblesPara($request->user())
            ->with(['tipoEvento', 'categorias'])
            ->conCuposOcupados()
            ->when($filtros['q'] ?? null, fn ($q, $texto) => $q->where('nombre', 'like', '%'.$texto.'%'))
            ->when($filtros['tipo'] ?? null, fn ($q, $tipo) => $q->where('tipo_evento_id', $tipo))
            ->orderByRaw('fecha_inicio < ? asc', [now()->toDateString()])
            ->orderBy('fecha_inicio')
            ->paginate(9)
            ->withQueryString();

        return view('eventos.index', [
            'eventos' => $eventos,
            'tipos' => TipoEvento::query()->orderBy('nombre')->pluck('nombre', 'id'),
            'filtros' => $filtros,
        ]);
    }

    public function show(Request $request, Evento $evento): View
    {
        $evento->load(['tipoEvento', 'categorias', 'requisitos', 'docente']);

        Gate::authorize('view', $evento);

        $user = $request->user();
        $user?->load('documentos');

        return view('eventos.show', [
            'evento' => $evento,
            'cuposDisponibles' => $evento->cuposDisponibles(),
            'inscripcion' => $user ? $evento->inscripciones()->whereBelongsTo($user)->first() : null,
        ]);
    }
}
