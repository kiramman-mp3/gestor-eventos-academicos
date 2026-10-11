<?php

namespace App\Models;

use App\Enums\RolUsuario;
use App\Enums\TipoDocumento;
use App\Enums\TipoUsuario;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario del sistema: estudiante, docente o administrador.
 * `rol` y `activo` no son asignables en masa: solo se cambian explícitamente.
 */
#[Fillable([
    'nombre', 'apellido', 'cedula', 'telefono', 'genero', 'fecha_nacimiento',
    'tipo', 'categoria_id', 'email', 'password',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $attributes = [
        'rol' => 'estudiante',
        'tipo' => 'publico',
        'activo' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
            'rol' => RolUsuario::class,
            'tipo' => TipoUsuario::class,
            'activo' => 'boolean',
        ];
    }

    /** Carrera / área académica del usuario institucional. */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    /** Eventos que el docente tiene asignados para calificar. */
    public function eventosAsignados(): HasMany
    {
        return $this->hasMany(Evento::class, 'docente_id');
    }

    public function solicitudesCambio(): HasMany
    {
        return $this->hasMany(SolicitudCambio::class);
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->nombre} {$this->apellido}"));
    }

    public function tieneRol(RolUsuario ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    public function esAdmin(): bool
    {
        return $this->rol === RolUsuario::Administrador;
    }

    public function esDocente(): bool
    {
        return $this->rol === RolUsuario::Docente;
    }

    public function esEstudiante(): bool
    {
        return $this->rol === RolUsuario::Estudiante;
    }

    public function documento(TipoDocumento $tipo): ?Documento
    {
        return $this->documentos->firstWhere('tipo', $tipo);
    }

    public function tieneDocumento(TipoDocumento $tipo): bool
    {
        return $this->documento($tipo) !== null;
    }
}
