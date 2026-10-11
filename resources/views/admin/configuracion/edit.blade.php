<x-app-layout title="Configuración">
    <x-admin.nav />
    <x-page-header title="Configuración general" subtitle="Datos de contacto que se muestran en el pie de página." />

    <form method="POST" action="{{ route('admin.configuracion.update') }}" class="card max-w-2xl space-y-4 p-6">
        @csrf
        @method('PUT')

        @foreach ($claves as $clave => $etiqueta)
            <x-field :name="$clave" :label="$etiqueta" :value="$valores[$clave] ?? null"
                     :type="$clave === 'email_contacto' ? 'email' : 'text'" :required="$clave === 'nombre_institucion'" />
        @endforeach

        <x-button>Guardar</x-button>
    </form>

    <section class="card mt-6 max-w-2xl p-6 text-sm text-zinc-600">
        <h2 class="font-semibold text-zinc-900">Sistema</h2>
        <dl class="mt-3 grid grid-cols-2 gap-2">
            <dt>Laravel</dt><dd>{{ app()->version() }}</dd>
            <dt>PHP</dt><dd>{{ PHP_VERSION }}</dd>
            <dt>Entorno</dt><dd>{{ app()->environment() }}</dd>
            <dt>Correo saliente</dt><dd>{{ config('mail.default') }}</dd>
        </dl>
    </section>
</x-app-layout>
