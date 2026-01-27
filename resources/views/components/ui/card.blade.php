@props([
    'as' => 'div',
])

<{{ $as }} {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-2xl shadow-sm dark:bg-slate-900 dark:border-slate-700']) }}>
    {{ $slot }}
</{{ $as }}>
