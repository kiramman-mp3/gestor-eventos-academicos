{{-- Notificaciones Toast flotantes y accesibles --}}
@if (session('status'))
    <div x-data="{ open: true }"
         x-init="setTimeout(() => open = false, 6000)"
         x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="fixed right-5 bottom-5 z-50 flex max-w-md items-start gap-3.5 rounded-2xl bg-emerald-600/95 px-5 py-4 text-sm font-medium text-white shadow-2xl backdrop-blur-md ring-1 ring-white/20"
         role="status">
        <div class="mt-0.5 rounded-full bg-white/20 p-1 shrink-0">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
        </div>
        <div class="flex-1 leading-snug">
            <p class="font-bold">¡Operación exitosa!</p>
            <p class="text-xs text-white/90 mt-0.5">{{ session('status') }}</p>
        </div>
        <button type="button" @click="open = false" class="rounded-lg p-1 text-white/80 transition hover:bg-white/20 hover:text-white" aria-label="Cerrar">&times;</button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ open: true }"
         x-show="open"
         x-transition.opacity
         class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50/90 p-4 text-sm text-rose-900 shadow-sm"
         role="alert">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
        <div class="flex-1">
            <strong class="font-semibold">Atención:</strong> {{ session('error') }}
        </div>
        <button type="button" @click="open = false" class="text-rose-400 hover:text-rose-700" aria-label="Cerrar">&times;</button>
    </div>
@endif
