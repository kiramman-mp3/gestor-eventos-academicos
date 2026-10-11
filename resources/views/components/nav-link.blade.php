@props(['active' => false])

<a {{ $attributes->merge(['class' => ($active ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white').' rounded-md px-3 py-2 text-sm font-medium transition']) }}
   @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
