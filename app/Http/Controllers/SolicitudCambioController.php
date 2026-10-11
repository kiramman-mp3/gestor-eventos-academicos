<?php

namespace App\Http\Controllers;

use App\Models\SolicitudCambio;
use App\Support\ArchivoPrivado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Solicitudes de cambio enviadas por cualquier usuario.
 * El solicitante se toma de la sesión; las capturas se guardan en el disco privado.
 */
class SolicitudCambioController extends Controller
{
    private const DISK = 'local';

    public function index(Request $request): View
    {
        return view('solicitudes.index', [
            'solicitudes' => $request->user()->solicitudesCambio()->with('resolucion')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('solicitudes.create', ['tipos' => SolicitudCambio::TIPOS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(SolicitudCambio::TIPOS)],
            'descripcion' => ['required', 'string', 'max:5000'],
            'justificacion' => ['nullable', 'string', 'max:5000'],
            'contexto' => ['nullable', 'string', 'max:5000'],
            'captura' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('fisei.uploads.imagen_max_kb')],
        ]);

        $solicitud = new SolicitudCambio(Arr::except($datos, 'captura'));
        $solicitud->user()->associate($request->user());
        if ($request->hasFile('captura')) {
            $solicitud->captura_path = $request->file('captura')->store('solicitudes', self::DISK);
        }
        $solicitud->save();

        return redirect()->route('solicitudes.index')->with('status', 'Solicitud enviada. Gracias por ayudarnos a mejorar.');
    }

    public function captura(SolicitudCambio $solicitud): StreamedResponse
    {
        Gate::authorize('view', $solicitud);
        abort_unless($solicitud->captura_path, 404);

        return ArchivoPrivado::respuesta(
            self::DISK,
            $solicitud->captura_path,
            'captura-'.$solicitud->id,
            Storage::disk(self::DISK)->mimeType($solicitud->captura_path) ?: 'application/octet-stream',
        );
    }
}
