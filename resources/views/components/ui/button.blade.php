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
        'primary' => 'bg-emerald-100 text-emerald-700 border border-emerald-200 hover:bg-emerald-200',
        'secondary' => 'bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200',
        'ghost' => 'text-slate-700 hover:text-slate-900',
        'danger' => 'bg-rose-100 text-rose-700 border border-rose-200 hover:bg-rose-200',
        'edit' => 'bg-amber-100 text-amber-700 border border-amber-200 hover:bg-amber-200',
        'success' => 'bg-emerald-100 text-emerald-700 border border-emerald-200 hover:bg-emerald-200',
    ];

    $classes = 'inline-flex items-center justify-center rounded-xl font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-400 ';
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
