<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Services\CertificadoPdfService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class CertificadoController extends Controller
{
    public function __construct(private readonly CertificadoPdfService $certificados) {}

    public function descargar(Inscripcion $inscripcion): Response
    {
        $inscripcion->load(['evento', 'user']);

        Gate::authorize('descargar-certificado', $inscripcion);

        return $this->certificados->generar($inscripcion)->download($this->certificados->nombreArchivo($inscripcion));
    }

    /** Verificación pública de autenticidad a partir del código impreso en el certificado. */
    public function verificar(string $codigo): View
    {
        $inscripcion = Inscripcion::query()
            ->where('codigo_certificado', $codigo)
            ->with(['user', 'evento'])
            ->first();

        return view('certificados.verificar', ['inscripcion' => $inscripcion, 'codigo' => $codigo]);
    }
}
