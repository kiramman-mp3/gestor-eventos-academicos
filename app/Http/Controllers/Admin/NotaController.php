<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoInscripcion;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarNotasRequest;
use App\Models\Evento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Registro de notas y asistencia. Lo usan administradores y el docente asignado al evento.
 * Solo se califican inscripciones con el pago confirmado.
 */
class NotaController extends Controller
{
    /** Eventos que el usuario puede calificar. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $eventos = Evento::query()
            ->when(! $user->esAdmin(), fn ($q) => $q->where('docente_id', $user->id))
            ->with('tipoEvento')
            ->withCount(['inscripciones as participantes' => fn ($q) => $q->where('estado', EstadoInscripcion::Pagado)])
            ->latest('fecha_inicio')
            ->paginate(15);

        return view('gestion.notas.index', ['eventos' => $eventos]);
    }

    public function edit(Evento $evento): View
    {
        Gate::authorize('calificar', $evento);

        $inscripciones = $evento->inscripciones()
            ->where('estado', EstadoInscripcion::Pagado)
            ->with('user')
            ->get()
            ->sortBy(fn ($i) => $i->user->apellido)
            ->each->setRelation('evento', $evento);

        return view('gestion.notas.edit', ['evento' => $evento, 'inscripciones' => $inscripciones]);
    }

    public function update(ActualizarNotasRequest $request, Evento $evento): RedirectResponse
    {
        $datos = $request->validated('inscripciones');

        DB::transaction(function () use ($evento, $datos) {
            // Solo inscripciones de este evento: los IDs ajenos enviados en el formulario se ignoran
            $inscripciones = $evento->inscripciones()
                ->where('estado', EstadoInscripcion::Pagado)
                ->whereKey(array_keys($datos))
                ->get();

            foreach ($inscripciones as $inscripcion) {
                $fila = $datos[$inscripcion->id];
                $inscripcion->nota = $evento->requiere_nota ? ($fila['nota'] ?? null) : null;
                $inscripcion->asistencia = $evento->requiere_asistencia ? ($fila['asistencia'] ?? null) : null;
                $inscripcion->legalizado = (bool) ($fila['legalizado'] ?? false);
                $inscripcion->save();
            }
        });

        return back()->with('status', 'Calificaciones guardadas.');
    }
}
