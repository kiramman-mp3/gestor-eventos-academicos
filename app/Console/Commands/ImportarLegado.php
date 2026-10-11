<?php

namespace App\Console\Commands;

use App\Enums\EstadoComprobante;
use App\Enums\EstadoEvento;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoSolicitud;
use App\Enums\Modalidad;
use App\Enums\Prioridad;
use App\Enums\RolUsuario;
use App\Enums\TipoContenido;
use App\Enums\TipoDocumento;
use App\Enums\TipoRequisito;
use App\Enums\TipoUsuario;
use App\Models\Categoria;
use App\Models\Comprobante;
use App\Models\Contenido;
use App\Models\Documento;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Resolucion;
use App\Models\SolicitudCambio;
use App\Models\TipoEvento;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Migra los datos del sistema PHP anterior (conexión `legado`) al nuevo esquema.
 *
 * Es idempotente: omite usuarios (por correo o cédula), eventos (por nombre y fecha de
 * inicio), inscripciones y solicitudes ya existentes y vuelve a enlazar sus ids, por lo
 * que puede ejecutarse varias veces. Los hashes de contraseña
 * (password_hash/bcrypt) son compatibles con Laravel y se copian tal cual.
 */
#[Signature('legado:importar
    {--uploads= : Ruta a la carpeta raíz del sistema anterior (la que contiene uploads/)}
    {--costo=0 : Costo a asignar a los eventos importados (el sistema anterior no lo guardaba)}')]
#[Description('Importa usuarios, eventos, inscripciones, archivos y contenido del sistema PHP anterior')]
class ImportarLegado extends Command
{
    private Connection $legado;

    private ?string $raiz = null;

    /** @var array<int, int> id legado => id nuevo */
    private array $categorias = [];

    private array $tipos = [];

    private array $usuarios = [];

    private array $eventos = [];

    private array $requisitos = [];

    private array $inscripciones = [];

    private array $avisos = [];

    public function handle(): int
    {
        $this->legado = DB::connection('legado');
        $this->raiz = $this->option('uploads') ? rtrim($this->option('uploads'), '/\\') : null;

        if ($this->raiz && ! is_dir($this->raiz.'/uploads')) {
            $this->error("No existe {$this->raiz}/uploads");

            return self::FAILURE;
        }

        if (! $this->raiz) {
            $this->warn('Sin --uploads: se importan los datos pero no los archivos.');
        }

        DB::transaction(function () {
            $this->tarea('Carreras', fn () => $this->importarCategorias());
            $this->tarea('Tipos de evento', fn () => $this->importarTipos());
            $this->tarea('Usuarios y documentos', fn () => $this->importarUsuarios());
            $this->tarea('Eventos y requisitos', fn () => $this->importarEventos());
            $this->tarea('Inscripciones y comprobantes', fn () => $this->importarInscripciones());
            $this->tarea('Contenido del sitio', fn () => $this->importarContenido());
            $this->tarea('Solicitudes de cambio', fn () => $this->importarSolicitudes());
        });

        foreach ($this->avisos as $aviso) {
            $this->warn("  · {$aviso}");
        }

        $this->info('Importación completada.');

        return self::SUCCESS;
    }

    private function tarea(string $nombre, callable $paso): void
    {
        $this->components->task($nombre, fn () => $paso() ?? true);
    }

    private function tabla(string $tabla): Collection
    {
        return $this->legado->getSchemaBuilder()->hasTable($tabla)
            ? $this->legado->table($tabla)->orderBy('id')->get()
            : collect();
    }

    private function importarCategorias(): void
    {
        foreach ($this->tabla('categorias_evento') as $fila) {
            // La categoría "Público" del sistema anterior pasa a ser el indicador es_publico
            if ($this->esPublico($fila->nombre)) {
                continue;
            }
            $this->categorias[$fila->id] = Categoria::query()->firstOrCreate(['nombre' => trim($fila->nombre)])->id;
        }
    }

    private function importarTipos(): void
    {
        foreach ($this->tabla('tipos_evento') as $fila) {
            $this->tipos[$fila->id] = TipoEvento::query()->firstOrCreate(['nombre' => trim($fila->nombre)])->id;
        }
    }

    private function importarUsuarios(): void
    {
        foreach ($this->tabla('estudiantes') as $fila) {
            $email = strtolower(trim($fila->correo));
            $existente = User::query()->withTrashed()->where('email', $email)->orWhere('cedula', $fila->cedula)->first();

            if ($existente) {
                $this->usuarios[$fila->id] = $existente->id;

                continue;
            }

            $user = new User;
            $user->forceFill([
                'nombre' => $fila->nombre,
                'apellido' => $fila->apellido,
                'cedula' => substr(preg_replace('/\D/', '', $fila->cedula), 0, 10),
                'telefono' => preg_match('/^09\d{8}$/', (string) $fila->telefono) ? $fila->telefono : null,
                'genero' => $fila->genero ?: null,
                'fecha_nacimiento' => $fila->fecha_nacimiento,
                'tipo' => TipoUsuario::tryFrom((string) $fila->tipo) ?? TipoUsuario::desdeEmail($email),
                'rol' => RolUsuario::tryFrom((string) $fila->rol) ?? RolUsuario::Estudiante,
                'categoria_id' => $this->categoriaDeCarrera($fila->carrera),
                'email' => $email,
                'password' => $fila->password,
                'email_verified_at' => ($fila->verificado ?? 0) ? ($fila->fecha_registro ?? now()) : null,
                'created_at' => $fila->fecha_registro ?? now(),
            ])->save();

            $this->usuarios[$fila->id] = $user->id;

            foreach (['cedula_path' => TipoDocumento::Cedula, 'papeleta_path' => TipoDocumento::Papeleta, 'matricula_path' => TipoDocumento::Matricula] as $columna => $tipo) {
                if ($archivo = $this->copiarArchivo($fila->{$columna} ?? null, Documento::DISK, Documento::DIRECTORIO.'/'.$user->id)) {
                    $user->documentos()->create(['tipo' => $tipo, ...$archivo]);
                }
            }
        }
    }

    private function importarEventos(): void
    {
        $pivot = $this->legado->getSchemaBuilder()->hasTable('evento_categoria')
            ? $this->legado->table('evento_categoria')->get()->groupBy('evento_id')
            : collect();
        $nombresLegado = $this->tabla('categorias_evento')->pluck('nombre', 'id');

        $requisitosLegado = $this->tabla('requisitos_evento')->groupBy('evento_id');

        foreach ($this->tabla('eventos') as $fila) {
            $requisitosFila = $requisitosLegado->get($fila->id, collect())->values();

            $existente = Evento::query()->withTrashed()
                ->where('nombre', $fila->nombre_evento)
                ->whereDate('fecha_inicio', $fila->fecha_inicio)
                ->first();

            // Ya importado en una ejecución anterior: solo se recupera el enlace de ids
            if ($existente) {
                $this->eventos[$fila->id] = $existente->id;
                foreach ($existente->requisitos()->orderBy('id')->pluck('id') as $i => $requisitoId) {
                    if ($req = $requisitosFila->get($i)) {
                        $this->requisitos[$req->id] = $requisitoId;
                    }
                }

                continue;
            }

            $legadoCategorias = collect([$fila->categoria_id ?? null])
                ->merge($pivot->get($fila->id, collect())->pluck('categoria_id'))
                ->filter()->unique();

            [$inicioInsc, $finInsc] = $this->fechasInscripcion($fila);

            $evento = new Evento;
            $evento->forceFill([
                'tipo_evento_id' => $this->tipos[$fila->tipo_evento_id] ?? TipoEvento::query()->firstOrCreate(['nombre' => 'Curso'])->id,
                'nombre' => $fila->nombre_evento,
                'descripcion' => $fila->descripcion,
                'ponentes' => $fila->ponentes ?: 'Por confirmar',
                'modalidad' => Modalidad::Presencial,
                'fecha_inicio' => $fila->fecha_inicio,
                'fecha_fin' => max($fila->fecha_inicio, $fila->fecha_fin),
                'fecha_inicio_inscripciones' => $inicioInsc,
                'fecha_fin_inscripciones' => $finInsc,
                'horas' => max(1, (int) $fila->horas),
                'cupos' => max(1, (int) $fila->cupos),
                'costo' => (float) $this->option('costo'),
                'es_publico' => $legadoCategorias->contains(fn ($id) => $this->esPublico($nombresLegado[$id] ?? '')),
                'requiere_nota' => (bool) ($fila->requiere_nota ?? false),
                'nota_minima' => ($fila->requiere_nota ?? false) ? ($fila->nota_minima ?? 7) : null,
                'requiere_asistencia' => (bool) ($fila->requiere_asistencia ?? false),
                'asistencia_minima' => ($fila->requiere_asistencia ?? false) ? ($fila->asistencia_minima ?? 70) : null,
                'estado' => match ($fila->estado) {
                    'cerrado' => EstadoEvento::Finalizado,
                    'en_ejecucion' => EstadoEvento::EnEjecucion,
                    default => EstadoEvento::Abierto,
                },
            ]);

            if ($imagen = $this->copiarArchivo($fila->ruta_imagen ?? null, 'public', 'eventos')) {
                $evento->imagen_path = $imagen['path'];
            }
            $evento->save();

            $evento->categorias()->sync($legadoCategorias->map(fn ($id) => $this->categorias[$id] ?? null)->filter()->values());
            $this->eventos[$fila->id] = $evento->id;

            foreach ($requisitosFila as $req) {
                $documento = $this->documentoDeCampo($req->campo_estudiante ?: $req->descripcion);
                $requisito = $evento->requisitos()->create([
                    'tipo' => $req->tipo === 'texto' ? TipoRequisito::Texto : ($documento ? TipoRequisito::Documento : TipoRequisito::Texto),
                    'tipo_documento' => $req->tipo === 'texto' ? null : $documento,
                    'descripcion' => $documento && $req->tipo !== 'texto' ? $documento->label() : $req->descripcion,
                ]);
                $this->requisitos[$req->id] = $requisito->id;
            }
        }
    }

    private function importarInscripciones(): void
    {
        $respuestas = $this->tabla('requisitos_inscripcion')->groupBy('inscripcion_id');

        foreach ($this->tabla('inscripciones') as $fila) {
            $userId = $this->usuarios[$fila->usuario_id] ?? null;
            $eventoId = $this->eventos[$fila->evento_id] ?? null;

            if (! $userId || ! $eventoId || Inscripcion::query()->where(['user_id' => $userId, 'evento_id' => $eventoId])->exists()) {
                $this->avisos[] = "Inscripción legado #{$fila->id} omitida (duplicada o sin usuario/evento).";

                continue;
            }

            $estado = match ($fila->estado) {
                'Esperando aprobación del admin' => EstadoInscripcion::PendienteAprobacion,
                'Pagado' => EstadoInscripcion::Pagado,
                'Error' => EstadoInscripcion::Error,
                default => EstadoInscripcion::EnEsperaPago,
            };

            $inscripcion = new Inscripcion;
            $inscripcion->forceFill([
                'user_id' => $userId,
                'evento_id' => $eventoId,
                'estado' => $estado,
                'nota' => $fila->nota !== null ? min(10, max(0, (float) $fila->nota)) : null,
                'asistencia' => $fila->asistencia !== null ? min(100, max(0, (float) $fila->asistencia)) : null,
                'legalizado' => (bool) ($fila->legalizado ?? false),
            ])->save();
            $this->inscripciones[$fila->id] = $inscripcion->id;

            if ($archivo = $this->copiarArchivo($fila->comprobante_pago ?? null, Comprobante::DISK, Comprobante::DIRECTORIO.'/'.$userId)) {
                $comprobante = $inscripcion->comprobantes()->make($archivo);
                $comprobante->estado = $estado === EstadoInscripcion::Pagado ? EstadoComprobante::Aprobado : EstadoComprobante::Pendiente;
                $comprobante->save();
            }

            foreach ($respuestas->get($fila->id, collect()) as $respuesta) {
                if (($requisitoId = $this->requisitos[$respuesta->requisito_id] ?? null) && filled($respuesta->texto_respuesta ?? null)) {
                    $inscripcion->respuestas()->create(['requisito_id' => $requisitoId, 'texto' => $respuesta->texto_respuesta]);
                }
            }
        }
    }

    private function importarContenido(): void
    {
        if (Contenido::query()->whereNot('tipo', TipoContenido::Mision)->whereNot('tipo', TipoContenido::Vision)->exists()) {
            $this->avisos[] = 'Contenido del sitio omitido: ya existe contenido en el nuevo sistema.';

            return;
        }

        foreach ($this->tabla('info_fisei') as $orden => $fila) {
            $tipo = TipoContenido::tryFrom((string) $fila->tipo);
            if (! $tipo) {
                continue;
            }

            $json = json_decode($fila->contenido, true);
            $datos = is_array($json) ? $json : ['texto' => $fila->contenido];

            $contenido = Contenido::query()->firstOrNew($tipo->esUnico() ? ['tipo' => $tipo] : ['tipo' => $tipo, 'titulo' => $datos['titulo'] ?? $datos['nombre'] ?? $datos['autor'] ?? null]);
            $contenido->fill([
                'tipo' => $tipo,
                'titulo' => $datos['titulo'] ?? $datos['nombre'] ?? $datos['autor'] ?? null,
                'subtitulo' => $datos['cargo'] ?? $datos['rol'] ?? null,
                'cuerpo' => $datos['descripcion'] ?? $datos['texto'] ?? null,
                'orden' => $orden,
            ]);
            if ($imagen = $this->copiarArchivo($datos['img'] ?? null, 'public', 'contenidos')) {
                $contenido->imagen_path = $imagen['path'];
            }
            $contenido->save();
        }
    }

    private function importarSolicitudes(): void
    {
        $resoluciones = $this->tabla('resoluciones')->groupBy('id_solicitud');

        foreach ($this->tabla('solicitudes') as $fila) {
            $userId = $this->usuarios[(int) $fila->uid] ?? User::query()->where('email', strtolower((string) $fila->uemail))->value('id');
            if (! $userId || SolicitudCambio::query()->where(['user_id' => $userId, 'titulo' => $fila->titulo])->exists()) {
                continue;
            }

            $solicitud = new SolicitudCambio;
            $solicitud->forceFill([
                'user_id' => $userId,
                'titulo' => $fila->titulo,
                'tipo' => $fila->tipo,
                'descripcion' => $fila->descripcion,
                'justificacion' => $fila->justificacion,
                'contexto' => $fila->contexto,
                'created_at' => $fila->creado_en ?? $fila->fecha,
            ])->save();

            $resolucion = $resoluciones->get($fila->id)?->last();
            if ($resolucion) {
                $nueva = new Resolucion;
                $nueva->forceFill([
                    'solicitud_cambio_id' => $solicitud->id,
                    'prioridad' => Prioridad::tryFrom(Str::lower(Str::ascii((string) $resolucion->prioridad))) ?? Prioridad::Media,
                    'estado' => Str::lower((string) $resolucion->estado) === 'terminado' ? EstadoSolicitud::Terminado : EstadoSolicitud::EnProgreso,
                    'comentario' => $resolucion->comentario ?: '—',
                ])->save();
            }
        }
    }

    /**
     * Copia un archivo del sistema anterior al disco indicado con un nombre aleatorio.
     *
     * @return array{path: string, nombre_original: string, mime: string, tamano: int}|null
     */
    private function copiarArchivo(?string $rutaLegado, string $disk, string $directorio): ?array
    {
        if (! $this->raiz || ! $rutaLegado) {
            return null;
        }

        $relativa = ltrim(preg_replace('#^(\.\./)+#', '', str_replace('\\', '/', $rutaLegado)), '/');
        $origen = realpath($this->raiz.'/'.$relativa);

        // Solo archivos dentro de la carpeta del sistema anterior
        if (! $origen || ! str_starts_with($origen, realpath($this->raiz)) || ! is_file($origen)) {
            $this->avisos[] = "Archivo no encontrado: {$rutaLegado}";

            return null;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($origen);
        $extension = match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };

        if (! $extension) {
            $this->avisos[] = "Archivo omitido por tipo no permitido ({$mime}): {$rutaLegado}";

            return null;
        }

        $path = $directorio.'/'.Str::random(40).'.'.$extension;
        Storage::disk($disk)->put($path, file_get_contents($origen));

        return ['path' => $path, 'nombre_original' => basename($origen), 'mime' => $mime, 'tamano' => filesize($origen)];
    }

    private function categoriaDeCarrera(mixed $carrera): ?int
    {
        if (blank($carrera) || $carrera === '0') {
            return null;
        }

        if (is_numeric($carrera)) {
            return $this->categorias[(int) $carrera] ?? null;
        }

        return Categoria::query()->whereRaw('LOWER(nombre) = ?', [Str::lower(trim($carrera))])->value('id');
    }

    private function documentoDeCampo(?string $campo): ?TipoDocumento
    {
        $campo = Str::lower((string) $campo);

        return match (true) {
            str_contains($campo, 'cedula') => TipoDocumento::Cedula,
            str_contains($campo, 'papeleta') => TipoDocumento::Papeleta,
            str_contains($campo, 'matricula') => TipoDocumento::Matricula,
            default => null,
        };
    }

    private function esPublico(string $nombre): bool
    {
        return in_array(Str::lower(Str::ascii(trim($nombre))), ['publico', 'publico general'], true);
    }

    /** Corrige fechas de inscripción que violan las reglas nuevas (cierre antes del inicio). */
    private function fechasInscripcion(object $fila): array
    {
        $inicio = CarbonImmutable::parse($fila->fecha_inicio);
        $inicioInsc = CarbonImmutable::parse($fila->fecha_inicio_inscripciones);
        $finInsc = CarbonImmutable::parse($fila->fecha_fin_inscripciones);

        if ($finInsc->greaterThan($inicio) || $inicioInsc->greaterThan($finInsc)) {
            $this->avisos[] = "Evento «{$fila->nombre_evento}»: fechas de inscripción ajustadas al inicio del evento.";
            $finInsc = $finInsc->min($inicio);
            $inicioInsc = $inicioInsc->min($finInsc);
        }

        return [$inicioInsc->toDateString(), $finInsc->toDateString()];
    }
}
