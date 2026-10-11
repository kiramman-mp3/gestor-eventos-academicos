@props(['color' => 'zinc'])

@php
    $colors = [
        'zinc' => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'amber' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'uta' => 'bg-uta-50 text-uta-700 ring-uta-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.($colors[$color] ?? $colors['zinc'])]) }}>
    {{ $slot }}
</span>
