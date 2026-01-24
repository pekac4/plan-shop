@props([
    'label' => null,
    'type' => 'text',
    'error' => null,
])

@php
    $errorKey = $error ?? $attributes->get('name');
    $inputId = $attributes->get('id') ?? $attributes->get('name');
    $baseClasses = 'w-full rounded-xl border bg-white px-3 py-2 text-slate-900 placeholder-slate-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500';
    $stateClasses = $errorKey && $errors->has($errorKey)
        ? 'border-red-300'
        : 'border-slate-300';
@endphp

<div class="grid gap-1">
    @if ($label)
        <label @if ($inputId) for="{{ $inputId }}" @endif class="text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        {{ $attributes->merge(['id' => $inputId, 'class' => $baseClasses.' '.$stateClasses]) }}
    />

    @if ($errorKey)
        @error($errorKey)
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
