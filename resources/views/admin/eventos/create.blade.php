<x-app-layout title="Nuevo evento">
    <x-admin.nav />
    <x-page-header title="Nuevo evento" />

    <form method="POST" action="{{ route('admin.eventos.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.eventos._form')

        <div class="mt-6 flex justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.eventos.index')">Cancelar</x-button>
            <x-button>Crear evento</x-button>
        </div>
    </form>
</x-app-layout>
