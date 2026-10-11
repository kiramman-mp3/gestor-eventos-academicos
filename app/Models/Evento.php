<?php

namespace App\Models;

use App\Enums\EstadoEvento;
use App\Enums\EstadoInscripcion;
use App\Enums\Modalidad;
use Database\Factories\EventoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'tipo_evento_id', 'docente_id', 'nombre', 'descripcion', 'ponentes', 'modalidad',
    'fecha_inicio', 'fecha_fin', 'fecha_inicio_inscripciones', 'fecha_fin_inscripciones',
    'horas', 'cupos', 'costo', 'es_publico',
    'requiere_nota', 'nota_minima', 'requiere_asistencia', 'asistencia_minima', 'estado',
])]
class Evento extends Model
{
    /** @use HasFactory<EventoFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_inicio_inscripciones' => 'date',
            'fecha_fin_inscripciones' => 'date',
            'horas' => 'integer',
            'cupos' => 'integer',
            'costo' => 'decimal:2',
            'es_publico' => 'boolean',
            'requiere_nota' => 'boolean',
            'nota_minima' => 'decimal:2',
            'requiere_asistencia' => 'boolean',
            'asistencia_minima' => 'decimal:2',
            'modalidad' => Modalidad::class,
            'estado' => EstadoEvento::class,
        ];
    }

    public function tipoEvento(): BelongsTo
    {
        return $this->belongsTo(TipoEvento::class);
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class);
    }

    public function requisitos(): HasMany
    {
        return $this->hasMany(Requisito::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    /** Eventos que el usuario puede ver en el catálogo. */
    #[Scope]
    protected function visiblesPara(Builder $query, ?User $user): void
    {
        if ($user?->esAdmin()) {
            return;
        }

        $query->whereIn('estado', [EstadoEvento::Abierto, EstadoEvento::EnEjecucion, EstadoEvento::Finalizado])
            ->where(function (Builder $q) use ($user) {
                $q->where('es_publico', true);

                if ($user?->categoria_id) {
                    $q->orWhereHas('categorias', fn (Builder $c) => $c->whereKey($user->categoria_id));
                }
            });
    }

    /** Agrega `cupos_ocupados` contando solo inscripciones que consumen cupo. */
    #[Scope]
    protected function conCuposOcupados(Builder $query): void
    {
        $query->withCount(['inscripciones as cupos_ocupados' => fn (Builder $q) => $q->whereIn('estado', EstadoInscripcion::ocupanCupo())]);
    }

    public function cuposOcupados(): int
    {
        return $this->cupos_ocupados
            ?? $this->inscripciones()->whereIn('estado', EstadoInscripcion::ocupanCupo())->count();
    }

    public function cuposDisponibles(): int
    {
        return max(0, $this->cupos - $this->cuposOcupados());
    }

    public function inscripcionAbierta(): bool
    {
        return $this->estado === EstadoEvento::Abierto
            && now()->startOfDay()->betweenIncluded($this->fecha_inicio_inscripciones, $this->fecha_fin_inscripciones);
    }

    public function esGratuito(): bool
    {
        return (float) $this->costo === 0.0;
    }

    /** El usuario cumple la regla de destinatarios (público o su carrera). */
    public function admiteA(User $user): bool
    {
        return $this->es_publico
            || ($user->categoria_id && $this->categorias->contains('id', $user->categoria_id));
    }

    public function esEditable(): bool
    {
        return in_array($this->estado, [EstadoEvento::Borrador, EstadoEvento::Abierto], true);
    }

    protected function imagenUrl(): Attribute
    {
        return Attribute::get(fn () => $this->imagen_path ? Storage::disk('public')->url($this->imagen_path) : null);
    }
}
