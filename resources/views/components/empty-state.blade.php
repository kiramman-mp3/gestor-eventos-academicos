@props(['title' => 'Sin resultados'])

<div {{ $attributes->merge(['class' => 'card px-6 py-12 text-center']) }}>
    <p class="text-base font-semibold text-zinc-800">{{ $title }}</p>
    @if ($slot->isNotEmpty())
        <div class="mt-2 text-sm text-zinc-500">{{ $slot }}</div>
    @endif
</div>
