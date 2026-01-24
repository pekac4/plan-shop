@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $sizeClasses = $size === 'sm'
        ? 'px-3 py-1.5 text-sm'
        : 'px-4 py-2 text-sm';

    $variants = [
        'primary' => 'bg-green-600 text-white hover:bg-green-700',
        'secondary' => 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50',
        'ghost' => 'text-slate-700 hover:text-slate-900',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
    ];

    $classes = 'inline-flex items-center justify-center rounded-xl font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 ';
    $classes .= $sizeClasses.' '.($variants[$variant] ?? $variants['secondary']);
@endphp

@if ($attributes->has('href'))
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
