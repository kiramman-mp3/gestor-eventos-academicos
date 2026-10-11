@php($sufijo = $contenido?->id ?? 'nuevo-'.$tipo->value)

@if ($tipo->admiteImagen())
    {{-- Imagen con lápiz al pasar el cursor y vista previa inmediata (editor legado) --}}
    <div x-data="{ vista: @js($contenido?->imagen_url) }">
        <label class="imagen-editable h-44 w-full" title="{{ $contenido ? 'Cambiar imagen' : 'Elegir imagen' }}">
            <img x-show="vista" :src="vista" alt="" class="h-full w-full object-cover">
            <span x-show="! vista" class="text-sm text-zinc-500">Haz clic para elegir una imagen</span>
            <span class="imagen-editable__icono" aria-hidden="true">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M4 20l4.3-.86L19.5 7.94a1.88 1.88 0 0 0 0-2.65l-.79-.79a1.88 1.88 0 0 0-2.65 0L4.86 15.7 4 20Z"/></svg>
            </span>
            <input id="imagen-{{ $sufijo }}" type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp" class="sr-only"
                   @change="const f = $event.target.files[0]; if (f) vista = URL.createObjectURL(f)">
        </label>
    </div>
@endif

@foreach ($tipo->campos() as $campo => $etiqueta)
    @if ($campo === 'cuerpo')
        <div>
            <x-input-label :for="$campo.'-'.$sufijo" :value="$etiqueta" required />
            <x-textarea :id="$campo.'-'.$sufijo" :name="$campo" rows="4" required maxlength="3000">{{ $contenido?->cuerpo }}</x-textarea>
        </div>
    @else
        <div>
            <x-input-label :for="$campo.'-'.$sufijo" :value="$etiqueta" required />
            <x-text-input :id="$campo.'-'.$sufijo" :name="$campo" :value="$contenido?->{$campo}" required maxlength="255" />
        </div>
    @endif
@endforeach

<div class="w-24">
    <x-input-label :for="'orden-'.$sufijo" value="Orden" />
    <x-text-input :id="'orden-'.$sufijo" type="number" name="orden" min="0" max="999" :value="$contenido?->orden ?? 0" />
</div>
