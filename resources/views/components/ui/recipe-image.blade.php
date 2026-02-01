@props([
    'id',
    'title' => '',
    'image' => null,
    'thumbnail' => null,
    'emoji' => '🥬',
    'containerClass' => 'h-12 w-14 overflow-hidden rounded-lg border border-slate-200 bg-slate-50',
    'imageClass' => 'h-full w-full object-cover',
    'placeholderClass' => 'text-lg',
])

@php
    $dialogId = $id;
    $thumbnailUrl = $thumbnail ?: $image;
    $modalImage = $image ?: $thumbnailUrl;
@endphp

<div class="{{ $containerClass }}">
    @if ($thumbnailUrl)
        <button type="button" class="block h-full w-full" onclick="document.getElementById('{{ $dialogId }}').showModal()">
            <img
                src="{{ $thumbnailUrl }}"
                alt="{{ $title }}"
                class="{{ $imageClass }}"
                loading="lazy"
            />
        </button>
    @else
        <div class="flex h-full w-full items-center justify-center {{ $placeholderClass }}">
            {{ $emoji }}
        </div>
    @endif
</div>

@if ($thumbnailUrl)
    <dialog
        id="{{ $dialogId }}"
        class="fixed inset-0 z-50 m-auto w-full max-w-none border-0 bg-transparent p-4 backdrop:bg-black/50 backdrop:backdrop-blur-xs"
    >
        <div class="relative mx-auto flex max-h-[85vh] w-full max-w-5xl items-center justify-center overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
            <img
                src="{{ $modalImage }}"
                alt="{{ $title }}"
                class="max-h-[85vh] w-full object-contain"
                loading="lazy"
            />
            <button
                type="button"
                class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-lg ring-1 ring-black/10 backdrop-blur hover:bg-white hover:text-slate-900 dark:bg-slate-800/90 dark:text-slate-100 dark:ring-white/10 dark:hover:bg-slate-800"
                aria-label="{{ __('Close') }}"
                onclick="document.getElementById('{{ $dialogId }}').close()"
            >
                ✕
            </button>
        </div>
    </dialog>
@endif
