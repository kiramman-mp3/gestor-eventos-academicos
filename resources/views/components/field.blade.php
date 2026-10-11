{{-- Campo de formulario completo: etiqueta + control + ayuda + errores de validación --}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'help' => null, 'options' => [], 'placeholder' => null])

@php
    $id = $attributes->get('id', str_replace(['[', ']', '.'], '_', $name));
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
    $current = in_array($type, ['password', 'file'], true) ? null : old($errorKey, $value);
    if ($current instanceof \BackedEnum) {
        $current = $current->value;
    } elseif ($current instanceof \DateTimeInterface) {
        $current = $current->format('Y-m-d');
    }
@endphp

<div {{ $attributes->only('class') }}>
    <x-input-label :for="$id" :value="$label" :required="$required" />

    @if ($type === 'textarea')
        <x-textarea :id="$id" :name="$name" :required="$required" {{ $attributes->except(['class', 'id'])->merge(['placeholder' => $placeholder]) }}>{{ $current }}</x-textarea>
    @elseif ($type === 'select')
        <x-select :id="$id" :name="$name" :required="$required" :options="$options" :selected="$current" :placeholder="$placeholder" {{ $attributes->except(['class', 'id']) }}>{{ $slot }}</x-select>
    @else
        <x-text-input :id="$id" :name="$name" :type="$type" :required="$required" :value="$current" {{ $attributes->except(['class', 'id'])->merge(['placeholder' => $placeholder]) }} />
    @endif

    @if ($help)
        <p class="mt-1 text-xs text-zinc-500">{{ $help }}</p>
    @endif

    <x-input-error :messages="$errors->get($errorKey)" />
</div>
