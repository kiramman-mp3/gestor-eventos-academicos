<?php

namespace Database\Seeders;

use App\Enums\RolUsuario;
use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\TipoEvento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Datos iniciales: administrador por defecto, carreras de la FISEI, tipos de evento
 * y contenido base de la página de inicio. Es idempotente: se puede ejecutar varias veces.
 */
class DatabaseSeeder extends Seeder
{
    private const CATEGORIAS = [
        'Software',
        'Tecnologías de la Información',
        'Telecomunicaciones',
        'Robótica',
        'Industrial',
    ];

    private const TIPOS_EVENTO = ['Curso', 'Webinar', 'Congreso', 'Taller', 'Evento FISEI'];

    public function run(): void
    {
        foreach (self::CATEGORIAS as $nombre) {
            Categoria::query()->firstOrCreate(['nombre' => $nombre]);
        }

        foreach (self::TIPOS_EVENTO as $nombre) {
            TipoEvento::query()->firstOrCreate(['nombre' => $nombre]);
        }

        $this->crearAdministrador();
        $this->call(ContenidoSeeder::class);

        Configuracion::guardar([
            'nombre_institucion' => 'Facultad de Ingeniería en Sistemas, Electrónica e Industrial',
            'email_contacto' => 'fisei@uta.edu.ec',
            'direccion' => 'Av. Los Chasquis y Río Payamino, Ambato',
        ]);
    }

    private function crearAdministrador(): void
    {
        $email = config('fisei.admin.email');

        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        $password = config('fisei.admin.password') ?: Str::password(16);

        $admin = new User([
            'nombre' => 'Administrador',
            'apellido' => 'FISEI',
            'cedula' => '1800000000',
            'email' => $email,
            'password' => $password,
        ]);
        $admin->rol = RolUsuario::Administrador;
        $admin->email_verified_at = now();
        $admin->save();

        if (! config('fisei.admin.password')) {
            $this->command?->warn("Administrador creado: {$email} / {$password} (cámbiala tras el primer ingreso).");
        }
    }

}
