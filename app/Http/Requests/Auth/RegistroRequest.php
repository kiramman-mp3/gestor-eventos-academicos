<?php

namespace App\Http\Requests\Auth;

use App\Enums\TipoUsuario;
use App\Models\User;
use App\Rules\CedulaEcuatoriana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegistroRequest extends FormRequest
{
    public const GENEROS = ['Masculino', 'Femenino', 'Otro', 'Prefiero no decirlo'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'nombre' => trim((string) $this->input('nombre')),
            'apellido' => trim((string) $this->input('apellido')),
        ]);
    }

    public function rules(): array
    {
        $institucional = TipoUsuario::desdeEmail((string) $this->input('email')) === TipoUsuario::Institucional;

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'cedula' => ['required', 'string', new CedulaEcuatoriana, Rule::unique(User::class)],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', Rule::unique(User::class)],
            'telefono' => ['required', 'regex:/^09\d{8}$/'],
            'genero' => ['required', Rule::in(self::GENEROS)],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:'.now()->subYears(config('fisei.edad_minima'))->toDateString()],
            // El correo @uta.edu.ec exige carrera; un correo público no puede declararla
            'categoria_id' => $institucional
                ? ['required', 'integer', Rule::exists('categorias', 'id')]
                : ['prohibited'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex' => 'El celular debe comenzar con 09 y tener 10 dígitos.',
            'fecha_nacimiento.before_or_equal' => 'Debes tener al menos '.config('fisei.edad_minima').' años para registrarte.',
            'categoria_id.required' => 'Selecciona tu carrera: usas un correo institucional.',
            'categoria_id.prohibited' => 'Solo los correos @'.TipoUsuario::DOMINIO_INSTITUCIONAL.' pueden indicar una carrera.',
        ];
    }

    public function attributes(): array
    {
        return [
            'categoria_id' => 'carrera',
            'fecha_nacimiento' => 'fecha de nacimiento',
        ];
    }
}
