<?php

namespace App\Http\Controllers;

use App\Enums\TipoDocumento;
use App\Http\Requests\SubirDocumentoRequest;
use App\Models\Documento;
use App\Support\ArchivoPrivado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documentos personales en el disco privado (storage/app/private/documentos/{user_id}).
 * Nunca son accesibles por URL pública: solo a través de show(), que aplica DocumentoPolicy.
 */
class DocumentoController extends Controller
{
    public function store(SubirDocumentoRequest $request): RedirectResponse
    {
        $user = $request->user();
        $tipo = TipoDocumento::from($request->validated('tipo'));
        $archivo = $request->file('archivo');

        // Nombre aleatorio (hashName) y extensión deducida del MIME real
        $path = $archivo->store(Documento::DIRECTORIO.'/'.$user->id, Documento::DISK);

        $anterior = $user->documentos()->where('tipo', $tipo)->first();

        DB::transaction(function () use ($user, $tipo, $archivo, $path) {
            $user->documentos()->updateOrCreate(['tipo' => $tipo], [
                'path' => $path,
                'nombre_original' => mb_substr($archivo->getClientOriginalName(), 0, 255),
                'mime' => $archivo->getMimeType(),
                'tamano' => $archivo->getSize(),
            ]);
        });

        if ($anterior && $anterior->path !== $path) {
            Storage::disk(Documento::DISK)->delete($anterior->path);
        }

        return back()->with('status', $tipo->label().' guardado correctamente.');
    }

    public function show(Documento $documento): StreamedResponse
    {
        Gate::authorize('view', $documento);

        return ArchivoPrivado::respuesta(Documento::DISK, $documento->path, $documento->tipo->value, $documento->mime);
    }

    public function destroy(Documento $documento): RedirectResponse
    {
        Gate::authorize('delete', $documento);

        $documento->delete();
        Storage::disk(Documento::DISK)->delete($documento->path);

        return back()->with('status', $documento->tipo->label().' eliminado.');
    }
}
