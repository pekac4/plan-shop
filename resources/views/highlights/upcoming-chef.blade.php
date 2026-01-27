<x-layouts::app :title="__('Upcoming Chef')">
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
        <div class="space-y-1 text-center">
            <div class="flex items-center justify-center gap-2 text-violet-600 dark:text-violet-300">
                <flux:icon.sparkles class="size-6" />
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Upcoming Chef') }}</h1>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-300">
                {{ __('Most copied recipes from') }} {{ $monthLabel }}.
            </p>
        </div>

        @if (! $leader || $leader->recipes_count < 1)
            <x-ui.card class="p-6">
                <div class="flex flex-col items-center justify-center gap-3 text-center">
                    <div class="text-3xl">🥬</div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('No rated recipes last month.') }}</p>
                </div>
            </x-ui.card>
        @else
            <x-ui.card class="mx-auto w-full max-w-lg overflow-hidden">
                <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-violet-100 text-2xl font-semibold text-violet-700 dark:bg-violet-900/40 dark:text-violet-200">
                            @if ($leader->avatar_url)
                                <img
                                    src="{{ $leader->avatar_url }}"
                                    alt="{{ $leader->name }}"
                                    class="h-full w-full object-cover"
                                />
                            @else
                                {{ $leader->initials() }}
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Upcoming Chef') }}</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $leader->name }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ __('Copied recipes') }}: <span class="font-medium text-slate-800 dark:text-slate-100">{{ $leader->recipes_count }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Top recipes last month') }}</h2>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('Copied at least once') }}</span>
                    </div>

                    <div class="mt-4 grid gap-3">
                        @foreach ($leaderRecipes as $recipe)
                            <a
                                href="{{ route('recipes.edit', $recipe->id) }}"
                                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-violet-200 hover:bg-violet-50/40 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-violet-400/40 dark:hover:bg-slate-800"
                            >
                                <div class="h-14 w-16 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900">
                                    @if ($recipe->cover_thumbnail_url)
                                        <img
                                            src="{{ $recipe->cover_thumbnail_url }}"
                                            alt="{{ $recipe->title }}"
                                            class="h-full w-full object-cover"
                                        />
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-lg">🥬</div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $recipe->title }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $recipe->last_month_copies ?? 0 }} {{ __('copies last month') }}
                                    </p>
                                </div>
                                <flux:icon.chevron-right class="size-4 text-slate-400 dark:text-slate-500" />
                            </a>
                        @endforeach
                    </div>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts::app>
