<?php

namespace App\Http\Requests;

use App\Enums\RolUsuario;
use App\Models\User;
use App\Rules\CedulaEcuatoriana;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Alta de personal (administradores y docentes) desde el panel. */
class CrearUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->esAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'cedula' => ['required', new CedulaEcuatoriana, Rule::unique(User::class)],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique(User::class)],
            'telefono' => ['nullable', 'regex:/^09\d{8}$/'],
            'rol' => ['required', Rule::in([RolUsuario::Administrador->value, RolUsuario::Docente->value])],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
