<x-app-layout :title="'Editar · '.$evento->nombre">
    <x-admin.nav />
    <x-page-header :title="'Editar: '.$evento->nombre" />

    <form method="POST" action="{{ route('admin.eventos.update', $evento) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.eventos._form')

        <div class="mt-6 flex justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.eventos.show', $evento)">Cancelar</x-button>
            <x-button>Guardar cambios</x-button>
        </div>
    </form>
</x-app-layout>
