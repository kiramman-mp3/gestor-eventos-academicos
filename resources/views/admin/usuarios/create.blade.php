<x-app-layout title="Nuevo usuario">
    <x-admin.nav />
    <x-page-header title="Nuevo administrador o docente" subtitle="La cuenta se crea verificada. Comparte la contraseña inicial por un canal seguro." />

    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="card grid max-w-3xl gap-4 p-6 sm:grid-cols-2">
        @csrf
        <x-field name="nombre" label="Nombres" required />
        <x-field name="apellido" label="Apellidos" required />
        <x-field name="cedula" label="Cédula" required inputmode="numeric" maxlength="10" />
        <x-field name="telefono" label="Celular" inputmode="numeric" maxlength="10" />
        <x-field class="sm:col-span-2" name="email" label="Correo" type="email" required />
        <x-field name="rol" label="Rol" type="select" required
                 :options="[\App\Enums\RolUsuario::Docente->value => 'Docente', \App\Enums\RolUsuario::Administrador->value => 'Administrador']" />
        <div class="hidden sm:block"></div>
        <x-field name="password" label="Contraseña inicial" type="password" required autocomplete="new-password" />
        <x-field name="password_confirmation" label="Confirmar contraseña" type="password" required autocomplete="new-password" />

        <div class="flex justify-end gap-2 sm:col-span-2">
            <x-button variant="secondary" :href="route('admin.usuarios.index')">Cancelar</x-button>
            <x-button>Crear usuario</x-button>
        </div>
    </form>
</x-app-layout>
