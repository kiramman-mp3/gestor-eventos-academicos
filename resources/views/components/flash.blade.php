@if (session('status'))
    <div x-data="{ open: true }" x-show="open" x-transition
         class="mb-6 flex items-start justify-between gap-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
        <span>{{ session('status') }}</span>
        <button type="button" @click="open = false" class="text-emerald-600 hover:text-emerald-900" aria-label="Cerrar">&times;</button>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
        {{ session('error') }}
    </div>
@endif
