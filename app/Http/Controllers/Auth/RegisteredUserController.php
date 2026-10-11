<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TipoUsuario;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistroRequest;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'categorias' => Categoria::query()->orderBy('nombre')->pluck('nombre', 'id'),
            'generos' => RegistroRequest::GENEROS,
        ]);
    }

    public function store(RegistroRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        // El rol nunca viene del formulario: todo registro público es estudiante
        $user = User::create([
            ...$datos,
            'tipo' => TipoUsuario::desdeEmail($datos['email']),
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
