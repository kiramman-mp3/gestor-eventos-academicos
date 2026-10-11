<x-app-layout title="Verificar certificado">
    <div class="mx-auto max-w-xl">
        <x-page-header title="Verificación de certificado" :subtitle="'Código: '.$codigo" />

        @if ($inscripcion)
            <div class="card border-l-4 border-l-emerald-500 p-6">
                <x-badge color="emerald">Certificado válido</x-badge>
                <dl class="mt-4 space-y-2 text-sm">
                    <div><dt class="font-medium text-zinc-500">Participante</dt><dd class="text-zinc-900">{{ $inscripcion->user->nombre_completo }}</dd></div>
                    <div><dt class="font-medium text-zinc-500">Evento</dt><dd class="text-zinc-900">{{ $inscripcion->evento->nombre }}</dd></div>
                    <div><dt class="font-medium text-zinc-500">Duración</dt><dd class="text-zinc-900">{{ $inscripcion->evento->horas }} horas</dd></div>
                    <div><dt class="font-medium text-zinc-500">Emitido</dt><dd class="text-zinc-900">{{ $inscripcion->certificado_emitido_at?->isoFormat('LL') ?? 'Fecha no registrada' }}</dd></div>
                </dl>
            </div>
        @else
            <div class="card border-l-4 border-l-rose-500 p-6">
                <x-badge color="rose">No encontrado</x-badge>
                <p class="mt-3 text-sm text-zinc-600">No existe un certificado emitido con este código.</p>
            </div>
        @endif
    </div>
</x-app-layout>
