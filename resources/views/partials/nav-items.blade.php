<x-nav-link :href="route('home')" :active="request()->routeIs('home')">Inicio</x-nav-link>
<x-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')">Eventos</x-nav-link>

@auth
    @if (auth()->user()->esAdmin())
        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">Administración</x-nav-link>
    @endif
    @if (auth()->user()->esDocente())
        <x-nav-link :href="route('gestion.notas.index')" :active="request()->routeIs('gestion.*')">Calificaciones</x-nav-link>
    @endif
    @if (auth()->user()->esEstudiante())
        <x-nav-link :href="route('inscripciones.index')" :active="request()->routeIs('inscripciones.*')">Mis inscripciones</x-nav-link>
    @endif
    <x-nav-link :href="route('perfil.edit')" :active="request()->routeIs('perfil.*')">Mi perfil</x-nav-link>
    <x-nav-link :href="route('solicitudes.index')" :active="request()->routeIs('solicitudes.*')">Solicitudes de cambio</x-nav-link>
@endauth
