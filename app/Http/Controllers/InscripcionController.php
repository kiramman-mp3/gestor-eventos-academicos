<?php

namespace App\Http\Controllers;

use App\Enums\EstadoInscripcion;
use App\Http\Requests\InscribirseRequest;
use App\Http\Requests\SubirComprobanteRequest;
use App\Models\Comprobante;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Services\InscripcionService;
use App\Support\ArchivoPrivado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InscripcionController extends Controller
{
    public function __construct(private readonly InscripcionService $inscripciones) {}

    /** "Mis inscripciones" del estudiante. */
    public function index(Request $request): View
    {
        $inscripciones = $request->user()->inscripciones()
            ->with(['evento.tipoEvento', 'comprobanteVigente'])
            ->latest()
            ->get();

        return view('inscripciones.index', ['inscripciones' => $inscripciones]);
    }

    public function store(InscribirseRequest $request, Evento $evento): RedirectResponse
    {
        $inscripcion = $this->inscripciones->inscribir($request->user(), $evento, $request->validated('respuestas', []));

        $mensaje = $inscripcion->estado === EstadoInscripcion::Pagado
            ? 'Inscripción confirmada. ¡Te esperamos!'
            : 'Inscripción registrada. Sube tu comprobante de pago para confirmar el cupo.';

        return redirect()->route('inscripciones.index')->with('status', $mensaje);
    }

    public function subirComprobante(SubirComprobanteRequest $request, Inscripcion $inscripcion): RedirectResponse
    {
        $this->inscripciones->subirComprobante($inscripcion, $request->file('comprobante'));

        return back()->with('status', 'Comprobante enviado. Te avisaremos cuando sea revisado.');
    }

    public function verComprobante(Comprobante $comprobante): StreamedResponse
    {
        Gate::authorize('verComprobante', $comprobante->inscripcion);

        return ArchivoPrivado::respuesta(Comprobante::DISK, $comprobante->path, 'comprobante-'.$comprobante->id, $comprobante->mime);
    }
}
