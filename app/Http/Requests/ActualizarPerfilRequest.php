<?php

namespace App\Http\Requests;

use App\Enums\TipoUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarPerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $user = $this->user();

        // La carrera solo se puede fijar una vez y solo para cuentas institucionales
        $puedeElegirCarrera = $user->tipo === TipoUsuario::Institucional && $user->categoria_id === null;

        return [
            'telefono' => ['required', 'regex:/^09\d{8}$/'],
            'categoria_id' => $puedeElegirCarrera
                ? ['required', 'integer', Rule::exists('categorias', 'id')]
                : ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex' => 'El celular debe comenzar con 09 y tener 10 dígitos.',
        ];
    }

    public function attributes(): array
    {
        return ['categoria_id' => 'carrera'];
    }
}
