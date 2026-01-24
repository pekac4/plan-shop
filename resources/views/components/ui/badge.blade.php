@props([
    'tone' => 'emerald',
])

@php
    $tones = [
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'lime' => 'bg-lime-100 text-lime-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'rose' => 'bg-rose-100 text-rose-700',
    ];

    $classes = 'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium';
    $classes .= ' '.($tones[$tone] ?? $tones['emerald']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
