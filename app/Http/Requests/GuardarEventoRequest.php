<?php

namespace App\Http\Requests;

use App\Enums\EstadoEvento;
use App\Enums\EstadoInscripcion;
use App\Enums\Modalidad;
use App\Enums\RolUsuario;
use App\Enums\TipoDocumento;
use App\Enums\TipoRequisito;
use App\Models\Evento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Crear o editar un evento. Reglas de fechas equivalentes a los CHECK de la base de datos:
 * inscripciones dentro de un rango válido y cerradas antes (o el mismo día) del inicio.
 */
class GuardarEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->esAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'es_publico' => $this->boolean('es_publico'),
            'requiere_nota' => $this->boolean('requiere_nota'),
            'requiere_asistencia' => $this->boolean('requiere_asistencia'),
            'categorias' => $this->input('categorias', []),
            'requisitos' => array_values($this->input('requisitos', [])),
        ]);
    }

    public function rules(): array
    {
        /** @var Evento|null $evento */
        $evento = $this->route('evento');
        $ocupados = $evento?->inscripciones()->whereIn('estado', EstadoInscripcion::ocupanCupo())->count() ?? 0;

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_evento_id' => ['required', 'integer', Rule::exists('tipos_evento', 'id')],
            'docente_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('rol', RolUsuario::Docente->value)],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'ponentes' => ['required', 'string', 'max:1000'],
            'modalidad' => ['required', Rule::enum(Modalidad::class)],
            'estado' => ['required', Rule::in([EstadoEvento::Borrador->value, EstadoEvento::Abierto->value])],

            'fecha_inicio_inscripciones' => ['required', 'date'],
            'fecha_fin_inscripciones' => ['required', 'date', 'after_or_equal:fecha_inicio_inscripciones', 'before_or_equal:fecha_inicio'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],

            'horas' => ['required', 'integer', 'min:1', 'max:1000'],
            // No se pueden quitar cupos ya ocupados
            'cupos' => ['required', 'integer', 'min:'.max(1, $ocupados), 'max:10000'],
            'costo' => ['required', 'numeric', 'min:0', 'max:10000'],

            'es_publico' => ['boolean'],
            'categorias' => ['array', 'required_if:es_publico,false'],
            'categorias.*' => ['integer', 'distinct', Rule::exists('categorias', 'id')],

            'requiere_nota' => ['boolean'],
            'nota_minima' => ['nullable', 'required_if:requiere_nota,true', 'numeric', 'between:0,10'],
            'requiere_asistencia' => ['boolean'],
            'asistencia_minima' => ['nullable', 'required_if:requiere_asistencia,true', 'numeric', 'between:0,100'],

            'imagen' => ['nullable', 'image', 'mimes:'.implode(',', config('fisei.uploads.imagen_mimes')), 'max:'.config('fisei.uploads.imagen_max_kb')],

            'requisitos' => ['array', 'max:20'],
            'requisitos.*.tipo' => ['required', Rule::enum(TipoRequisito::class)],
            'requisitos.*.tipo_documento' => ['nullable', 'required_if:requisitos.*.tipo,'.TipoRequisito::Documento->value, Rule::enum(TipoDocumento::class)],
            'requisitos.*.descripcion' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_fin_inscripciones.before_or_equal' => 'Las inscripciones deben cerrar antes o el mismo día del inicio del evento.',
            'categorias.required_if' => 'Selecciona al menos una carrera o marca el evento como abierto al público.',
            'cupos.min' => 'Los cupos no pueden ser menores que las inscripciones activas (:min).',
            'requisitos.*.tipo_documento.required_if' => 'Indica qué documento del perfil se exige.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo_evento_id' => 'tipo de evento',
            'docente_id' => 'docente',
            'fecha_inicio_inscripciones' => 'inicio de inscripciones',
            'fecha_fin_inscripciones' => 'fin de inscripciones',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
            'nota_minima' => 'nota mínima',
            'asistencia_minima' => 'asistencia mínima',
            'requisitos.*.descripcion' => 'descripción del requisito',
        ];
    }

    /** Datos del evento sin relaciones ni archivos. */
    public function datosEvento(): array
    {
        $datos = $this->safe()->except(['categorias', 'requisitos', 'imagen']);

        $datos['nota_minima'] = $datos['requiere_nota'] ? $datos['nota_minima'] : null;
        $datos['asistencia_minima'] = $datos['requiere_asistencia'] ? $datos['asistencia_minima'] : null;

        return $datos;
    }
}
