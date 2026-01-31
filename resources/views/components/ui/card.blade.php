@props([
    'as' => 'div',
])

@php
    $classes = 'border border-slate-200 rounded-2xl shadow-sm dark:border-slate-700';
    $hasBackground = str_contains($attributes->get('class', ''), 'bg-');

    if (! $hasBackground) {
        $classes .= ' bg-white dark:bg-slate-900';
    }
@endphp

<{{ $as }} {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $as }}>
