@props(['variant' => 'primary', 'href' => null, 'size' => 'md'])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60';
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
    ];
    $variants = [
        'primary' => 'bg-uta-600 text-white shadow-sm hover:bg-uta-700 focus:ring-uta-500',
        'secondary' => 'border border-zinc-300 bg-white text-zinc-700 shadow-sm hover:bg-zinc-50 focus:ring-zinc-400',
        'dark' => 'bg-marino-800 text-white shadow-sm hover:bg-marino-900 focus:ring-marino-700',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700 focus:ring-rose-500',
        'success' => 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 focus:ring-emerald-500',
        'ghost' => 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus:ring-zinc-300',
    ];
    $classes = $base.' '.$sizes[$size].' '.$variants[$variant];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
