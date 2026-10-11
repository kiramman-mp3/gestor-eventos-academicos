<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoComprobante;
use App\Http\Controllers\Controller;
use App\Models\Comprobante;
use App\Services\InscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Revisión de comprobantes de pago por la administración. */
class ComprobanteController extends Controller
{
    public function __construct(private readonly InscripcionService $inscripciones) {}

    public function index(Request $request): View
    {
        $estado = EstadoComprobante::tryFrom((string) $request->query('estado')) ?? EstadoComprobante::Pendiente;

        $comprobantes = Comprobante::query()
            ->where('estado', $estado)
            ->with(['inscripcion.user', 'inscripcion.evento', 'revisor'])
            ->oldest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.comprobantes.index', [
            'comprobantes' => $comprobantes,
            'estado' => $estado,
        ]);
    }

    public function aprobar(Request $request, Comprobante $comprobante): RedirectResponse
    {
        $this->inscripciones->aprobarComprobante($comprobante, $request->user());

        return back()->with('status', 'Pago aprobado: la inscripción quedó confirmada.');
    }

    public function rechazar(Request $request, Comprobante $comprobante): RedirectResponse
    {
        $motivo = $request->validate(['motivo' => ['required', 'string', 'min:5', 'max:500']])['motivo'];

        $this->inscripciones->rechazarComprobante($comprobante, $request->user(), $motivo);

        return back()->with('status', 'Comprobante rechazado. El estudiante podrá subir uno nuevo.');
    }
}
