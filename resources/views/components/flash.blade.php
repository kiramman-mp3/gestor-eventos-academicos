{{-- Confirmaciones como aviso flotante (toast del sitio legado); los errores quedan fijos en la página --}}
@if (session('status'))
    <div x-data="{ open: true }" x-init="setTimeout(() => open = false, 5000)" x-show="open" x-transition.opacity
         class="fixed right-5 bottom-5 z-50 flex max-w-sm items-start gap-3 rounded-lg bg-emerald-600 px-5 py-4 text-sm font-medium text-white shadow-xl" role="status">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 3 3 5-6"/></svg>
        <span>{{ session('status') }}</span>
        <button type="button" @click="open = false" class="ml-2 text-white/80 hover:text-white" aria-label="Cerrar">&times;</button>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
        {{ session('error') }}
    </div>
@endif
