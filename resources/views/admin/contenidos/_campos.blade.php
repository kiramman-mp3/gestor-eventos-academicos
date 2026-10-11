@php($sufijo = $contenido?->id ?? 'nuevo-'.$tipo->value)

@if ($contenido?->imagen_url)
    <img src="{{ $contenido->imagen_url }}" alt="" class="h-36 w-full rounded-lg object-cover">
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

<div class="grid grid-cols-[1fr_6rem] gap-3">
    @if ($tipo->admiteImagen())
        <div>
            <x-input-label :for="'imagen-'.$sufijo" value="Imagen" />
            <input id="imagen-{{ $sufijo }}" type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp"
                   class="block w-full text-xs text-zinc-600 file:mr-2 file:rounded-md file:border-0 file:bg-zinc-100 file:px-2 file:py-1 file:text-xs file:font-semibold">
        </div>
    @else
        <div></div>
    @endif
    <div>
        <x-input-label :for="'orden-'.$sufijo" value="Orden" />
        <x-text-input :id="'orden-'.$sufijo" type="number" name="orden" min="0" max="999" :value="$contenido?->orden ?? 0" />
    </div>
</div>
