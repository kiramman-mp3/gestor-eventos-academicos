<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEvento;
use App\Enums\Modalidad;
use App\Enums\RolUsuario;
use App\Enums\TipoDocumento;
use App\Enums\TipoRequisito;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarEventoRequest;
use App\Models\Categoria;
use App\Models\Evento;
use App\Models\TipoEvento;
use App\Models\User;
use App\Services\EventoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventoAdminController extends Controller
{
    public function __construct(private readonly EventoService $eventos) {}

    public function index(Request $request): View
    {
        $estado = EstadoEvento::tryFrom((string) $request->query('estado'));

        $eventos = Evento::query()
            ->with('tipoEvento')
            ->conCuposOcupados()
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($request->query('q'), fn ($q, $texto) => $q->where('nombre', 'like', '%'.$texto.'%'))
            ->latest('fecha_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('admin.eventos.index', ['eventos' => $eventos, 'estado' => $estado]);
    }

    public function create(): View
    {
        return view('admin.eventos.create', $this->datosFormulario(new Evento([
            'modalidad' => Modalidad::Presencial,
            'estado' => EstadoEvento::Borrador,
            'costo' => 0,
        ])));
    }

    public function store(GuardarEventoRequest $request): RedirectResponse
    {
        $evento = $this->eventos->guardar(
            new Evento,
            $request->datosEvento(),
            $request->validated('categorias', []),
            $request->validated('requisitos', []),
            $request->file('imagen'),
        );

        return redirect()->route('admin.eventos.show', $evento)->with('status', 'Evento creado.');
    }

    public function show(Evento $evento): View
    {
        $evento->load(['tipoEvento', 'categorias', 'requisitos', 'docente'])->loadCount('inscripciones');

        $inscripciones = $evento->inscripciones()
            ->with(['user.documentos', 'respuestas.requisito', 'comprobanteVigente'])
            ->get()
            ->sortBy(fn ($i) => $i->user->apellido);

        return view('admin.eventos.show', [
            'evento' => $evento,
            'inscripciones' => $inscripciones,
            'cuposDisponibles' => $evento->cuposDisponibles(),
        ]);
    }

    public function edit(Evento $evento): View|RedirectResponse
    {
        if (! $evento->esEditable()) {
            return redirect()->route('admin.eventos.show', $evento)->with('error', 'Solo se pueden editar eventos en borrador o abiertos.');
        }

        return view('admin.eventos.edit', $this->datosFormulario($evento->load(['categorias', 'requisitos'])->loadCount('inscripciones')));
    }

    public function update(GuardarEventoRequest $request, Evento $evento): RedirectResponse
    {
        abort_unless($evento->esEditable(), 403, 'Solo se pueden editar eventos en borrador o abiertos.');

        $this->eventos->guardar(
            $evento,
            $request->datosEvento(),
            $request->validated('categorias', []),
            $request->validated('requisitos', []),
            $request->file('imagen'),
        );

        return redirect()->route('admin.eventos.show', $evento)->with('status', 'Evento actualizado.');
    }

    public function cambiarEstado(Request $request, Evento $evento): RedirectResponse
    {
        $estado = EstadoEvento::from($request->validate([
            'estado' => ['required', Rule::enum(EstadoEvento::class)],
        ])['estado']);

        $this->eventos->cambiarEstado($evento, $estado);

        return back()->with('status', "Estado actualizado a «{$estado->label()}».");
    }

    public function destroy(Evento $evento): RedirectResponse
    {
        $this->eventos->eliminar($evento);

        return redirect()->route('admin.eventos.index')->with('status', 'Evento eliminado.');
    }

    private function datosFormulario(Evento $evento): array
    {
        return [
            'evento' => $evento,
            'tipos' => TipoEvento::query()->orderBy('nombre')->pluck('nombre', 'id'),
            'categorias' => Categoria::query()->orderBy('nombre')->pluck('nombre', 'id'),
            'docentes' => User::query()->where('rol', RolUsuario::Docente)->orderBy('apellido')->get()
                ->mapWithKeys(fn (User $u) => [$u->id => $u->nombre_completo]),
            'tiposRequisito' => TipoRequisito::options(),
            'tiposDocumento' => TipoDocumento::options(),
        ];
    }
}
