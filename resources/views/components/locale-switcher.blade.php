<div {{ $attributes->merge(['class' => 'flex items-center gap-1']) }}>
    <form method="POST" action="{{ route('locale.switch') }}">
        @csrf
        <input type="hidden" name="locale" value="en" />
        <flux:button
            type="submit"
            size="sm"
            variant="ghost"
            @class([
                '!px-2 !border !border-emerald-200 !bg-emerald-100 !text-emerald-900 shadow-sm' => app()->getLocale() === 'en',
                '!border !border-transparent !bg-transparent text-slate-600 hover:text-slate-900' => app()->getLocale() !== 'en',
            ])
            aria-label="{{ __('Switch to English') }}"
            data-test="locale-en"
        >
            <span class="text-base">🇬🇧</span>
            <span class="text-xs font-semibold">EN</span>
        </flux:button>
    </form>

    <form method="POST" action="{{ route('locale.switch') }}">
        @csrf
        <input type="hidden" name="locale" value="sr" />
        <flux:button
            type="submit"
            size="sm"
            variant="ghost"
            @class([
                '!px-2 !border !border-emerald-200 !bg-emerald-100 !text-emerald-900 shadow-sm' => app()->getLocale() === 'sr',
                '!border !border-transparent !bg-transparent text-slate-600 hover:text-slate-900' => app()->getLocale() !== 'sr',
            ])
            aria-label="{{ __('Switch to Serbian') }}"
            data-test="locale-sr"
        >
            <span class="text-base">🇷🇸</span>
            <span class="text-xs font-semibold">SR</span>
        </flux:button>
    </form>
</div>
