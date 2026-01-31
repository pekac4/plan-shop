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

@if ($image)
    <dialog id="{{ $dialogId }}" class="m-auto w-full max-w-4xl rounded-2xl bg-transparent p-0 backdrop:bg-black/40">
        <div class="relative max-h-[85vh] w-full">
            <img
                src="{{ $image }}"
                alt="{{ $title }}"
                class="h-full w-full rounded-2xl object-contain shadow-lg"
                loading="lazy"
            />
            <form method="dialog">
                <button
                    type="submit"
                    class="absolute -top-4 -right-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow hover:text-slate-900 dark:bg-slate-800 dark:text-slate-200 dark:hover:text-white"
                    aria-label="{{ __('Close') }}"
                >
                    ✕
                </button>
            </form>
        </div>
    </dialog>
@endif
