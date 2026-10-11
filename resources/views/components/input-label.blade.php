@props(['value' => null, 'required' => false])

<label {{ $attributes->merge(['class' => 'mb-1 block text-sm font-medium text-zinc-700']) }}>
    {{ $value ?? $slot }}
    @if ($required)
        <span class="text-uta-600" aria-hidden="true">*</span>
    @endif
</label>
