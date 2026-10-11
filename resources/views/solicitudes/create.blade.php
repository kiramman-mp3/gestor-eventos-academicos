<x-app-layout title="Nueva solicitud de cambio">
    <x-page-header title="Solicitud de cambio" subtitle="Reporta un error o propone una mejora del sistema." />

    <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data" class="card grid max-w-3xl gap-4 p-6">
        @csrf
        <x-field name="titulo" label="Título" required maxlength="255" />
        <x-field name="tipo" label="Tipo" type="select" required placeholder="Selecciona" :options="array_combine($tipos, $tipos)" />
        <x-field name="descripcion" label="Descripción del cambio" type="textarea" rows="5" required />
        <x-field name="justificacion" label="Justificación" type="textarea" rows="3" />
        <x-field name="contexto" label="Contexto (dónde ocurre, pasos para reproducirlo…)" type="textarea" rows="3" />
        <div>
            <x-input-label for="captura" value="Captura de pantalla (opcional)" />
            <input id="captura" type="file" name="captura" accept=".jpg,.jpeg,.png,.webp"
                   class="block w-full text-sm text-zinc-600 file:mr-2 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-semibold">
            <x-input-error :messages="$errors->get('captura')" />
        </div>
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" :href="route('solicitudes.index')">Cancelar</x-button>
            <x-button>Enviar solicitud</x-button>
        </div>
    </form>
</x-app-layout>
