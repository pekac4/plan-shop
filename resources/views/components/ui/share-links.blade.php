@props([
    'url',
    'text' => __('Check out Plan&Shop.'),
    'label' => null,
])

@php
    $shareUrl = $url ?? url('/');
    $encodedUrl = rawurlencode($shareUrl);
    $encodedText = rawurlencode($text);
    $encodedSubject = rawurlencode($text);
    $encodedBody = rawurlencode($text."\n".$shareUrl);

    $links = [
        [
            'name' => 'X',
            'label' => __('Share on X'),
            'href' => "https://x.com/intent/tweet?text={$encodedText}&url={$encodedUrl}",
        ],
        [
            'name' => 'Facebook',
            'label' => __('Share on Facebook'),
            'href' => "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}",
        ],
        [
            'name' => 'Instagram',
            'label' => __('Share on Instagram'),
            'href' => "https://www.instagram.com/?url={$encodedUrl}",
        ],
        [
            'name' => 'Gmail',
            'label' => __('Share via Gmail'),
            'href' => "https://mail.google.com/mail/?view=cm&fs=1&su={$encodedSubject}&body={$encodedBody}",
        ],
    ];
@endphp

<div class="flex flex-wrap items-center gap-2">
    @if ($label)
        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
            {{ $label }}
        </span>
    @endif

    <div class="flex items-center gap-2">
        @foreach ($links as $link)
            <a
                href="{{ $link['href'] }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="{{ $link['label'] }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:text-slate-100"
            >
                @if ($link['name'] === 'X')
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">
                        <path d="M18.9 3H22l-7.6 8.7L23 21h-6.7l-5.2-6-5.2 6H2.9l8.2-9.4L1 3h6.8l4.7 5.4L18.9 3Zm-1.2 16.1h1.9L7.4 4.7H5.4l12.3 14.4Z" />
                    </svg>
                @elseif ($link['name'] === 'Facebook')
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">
                        <path d="M13 9h3V6h-3c-2.2 0-4 1.8-4 4v3H7v3h2v6h3v-6h3l1-3h-4v-3c0-.6.4-1 1-1Z" />
                    </svg>
                @elseif ($link['name'] === 'Instagram')
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">
                        <path d="M16.5 3h-9A4.5 4.5 0 0 0 3 7.5v9A4.5 4.5 0 0 0 7.5 21h9a4.5 4.5 0 0 0 4.5-4.5v-9A4.5 4.5 0 0 0 16.5 3Zm3 13.5a3 3 0 0 1-3 3h-9a3 3 0 0 1-3-3v-9a3 3 0 0 1 3-3h9a3 3 0 0 1 3 3v9Z" />
                        <path d="M12 7.5A4.5 4.5 0 1 0 16.5 12 4.5 4.5 0 0 0 12 7.5Zm0 7.5A3 3 0 1 1 15 12a3 3 0 0 1-3 3Z" />
                        <path d="M17.2 6.8a1.1 1.1 0 1 0 1.1 1.1 1.1 1.1 0 0 0-1.1-1.1Z" />
                    </svg>
                @elseif ($link['name'] === 'Gmail')
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">
                        <path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm0 2v.4l8 5.1 8-5.1V7H4Zm16 10V9.6l-7.5 4.7a1 1 0 0 1-1 0L4 9.6V17h16Z" />
                    </svg>
                @endif
                <span class="sr-only">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
