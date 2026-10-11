<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Notas (0-10), asistencia (0-100 %) y legalización por inscripción.
 * Una nota por debajo del mínimo es un resultado válido (reprobado), no un error de entrada.
 */
class ActualizarNotasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('calificar', $this->route('evento'));
    }

    public function rules(): array
    {
        return [
            'inscripciones' => ['required', 'array'],
            'inscripciones.*.nota' => ['nullable', 'numeric', 'between:0,10'],
            'inscripciones.*.asistencia' => ['nullable', 'numeric', 'between:0,100'],
            'inscripciones.*.legalizado' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'inscripciones.*.nota' => 'nota',
            'inscripciones.*.asistencia' => 'asistencia',
        ];
    }
}
