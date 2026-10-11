<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EstadoEvento;
use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Evento;
use App\Models\Inscripcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Configuración general del sitio (antes admin/configuracion_sistema.php). */
class ConfiguracionController extends Controller
{
    public function edit(): View
    {
        return view('admin.configuracion.edit', [
            'grupos' => Configuracion::GRUPOS,
            'valores' => Configuracion::guardados(),
            'estadisticas' => [
                'Eventos activos' => Evento::query()->whereIn('estado', [EstadoEvento::Abierto, EstadoEvento::EnEjecucion])->count(),
                'Inscripciones' => Inscripcion::query()->count(),
                'Archivos subidos' => $this->espacioUsado(),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $grupo = $request->validate(['grupo' => ['required', Rule::in(array_keys(Configuracion::GRUPOS))]])['grupo'];
        $campos = Configuracion::GRUPOS[$grupo]['campos'];
        $datos = $request->validate(Configuracion::reglas($grupo));

        foreach ($campos as $clave => $campo) {
            if ($campo['tipo'] !== 'imagen') {
                continue;
            }

            unset($datos[$clave]);
            $anterior = Configuracion::guardados()[$clave] ?? null;

            if ($request->hasFile($clave)) {
                $datos[$clave] = $request->file($clave)->store(Configuracion::DIRECTORIO, Configuracion::DISK);
            } elseif ($request->boolean("quitar_{$clave}")) {
                $datos[$clave] = null;
            } else {
                continue;
            }

            if ($anterior) {
                Storage::disk(Configuracion::DISK)->delete($anterior);
            }
        }

        Configuracion::guardar($datos);

        return redirect()->route('admin.configuracion.edit', ['seccion' => $grupo])
            ->with('status', Configuracion::GRUPOS[$grupo]['titulo'].': cambios guardados.');
    }

    /** Copia de seguridad de la configuración en JSON (las imágenes se exportan como ruta). */
    public function exportar(): StreamedResponse
    {
        $datos = [
            'exportado' => now()->toIso8601String(),
            'configuracion' => array_intersect_key(Configuracion::guardados(), Configuracion::campos()),
        ];

        return response()->streamDownload(
            fn () => print (json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'configuracion-'.now()->format('Y-m-d').'.json',
            ['Content-Type' => 'application/json'],
        );
    }

    public function limpiarCache(): RedirectResponse
    {
        Cache::flush();
        Artisan::call('view:clear');

        return back()->with('status', 'Caché del sistema limpiada.');
    }

    private function espacioUsado(): string
    {
        $bytes = 0;

        foreach (['public', 'local'] as $disco) {
            $storage = Storage::disk($disco);
            foreach ($storage->allFiles() as $archivo) {
                $bytes += $storage->size($archivo);
            }
        }

        return number_format($bytes / 1024 / 1024, 1).' MB';
    }
}
