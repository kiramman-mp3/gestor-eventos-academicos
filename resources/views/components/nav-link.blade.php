@props(['active' => false])

<a {{ $attributes->merge(['class' => ($active ? 'border-uta-600 font-bold text-uta-700' : 'border-transparent font-medium text-tinta hover:text-uta-700').' border-b-2 py-3 text-[0.95rem] transition md:py-3.5']) }}
   @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
