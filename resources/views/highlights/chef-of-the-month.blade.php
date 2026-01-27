<x-layouts::app :title="__('Chef of the Month')">
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
        <div class="space-y-1 text-center">
            <div class="flex items-center justify-center gap-2 text-emerald-700 dark:text-emerald-300">
                <flux:icon.chef-hat class="size-6" />
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Chef of the Month') }}</h1>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Celebrating the top recipe from') }} {{ $monthLabel }}.</p>
        </div>

        @if (! $recipe)
            <x-ui.card class="p-6">
                <div class="flex flex-col items-center justify-center gap-3 text-center">
                    <div class="text-3xl">🥬</div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('No public recipes were rated last month yet.') }}</p>
                </div>
            </x-ui.card>
        @else
            <x-ui.card class="mx-auto w-full max-w-lg overflow-hidden">
                <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-emerald-100 text-2xl font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                            @if ($recipe->user?->avatar_url)
                                <img
                                    src="{{ $recipe->user->avatar_url }}"
                                    alt="{{ $recipe->user->name }}"
                                    class="h-full w-full object-cover"
                                />
                            @else
                                {{ $recipe->user?->initials() ?? 'PS' }}
                            @endif
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Chef of the Month') }}</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $recipe->user?->name ?? __('Unknown chef') }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ __('Earned') }} {{ $recipe->stars ?? 0 }} {{ __('stars with') }}
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $recipe->title }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="md:ms-auto">
                        <x-ui.button :href="route('recipes.edit', $recipe->id)" variant="secondary">
                            {{ __('View recipe') }}
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="overflow-hidden">
                <div class="grid gap-6 p-6 md:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                    <div class="space-y-4">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900">
                            @if ($recipe->cover_image_url)
                                <img
                                    src="{{ $recipe->cover_image_url }}"
                                    alt="{{ $recipe->title }}"
                                    class="h-64 w-full object-cover md:h-72"
                                />
                            @else
                                <div class="flex h-64 items-center justify-center text-4xl">🥬</div>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $recipe->title }}</h2>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ __('By') }} {{ $recipe->user?->name ?? __('Unknown chef') }}
                            </p>
                            <p class="text-sm text-slate-700 dark:text-slate-200">
                                {{ $recipe->description ?: __('A community favorite for the month.') }}
                            </p>
                            <div class="flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ __('Servings') }}: {{ $recipe->servings }}</span>
                                @if ($recipe->prep_time_minutes)
                                    <span>{{ __('Prep') }}: {{ $recipe->prep_time_minutes }} {{ __('min') }}</span>
                                @endif
                                @if ($recipe->cook_time_minutes)
                                    <span>{{ __('Cook') }}: {{ $recipe->cook_time_minutes }} {{ __('min') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ __('Ingredients') }}</h3>
                            <ul class="mt-2 space-y-2 text-sm text-slate-700 dark:text-slate-200">
                                @forelse ($recipe->ingredients as $ingredient)
                                    <li class="flex items-start justify-between gap-2">
                                        <span>{{ $ingredient->name }}</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">
                                            @if ($ingredient->quantity)
                                                {{ rtrim(rtrim(number_format((float) $ingredient->quantity, 2, '.', ''), '0'), '.') }}
                                                {{ $ingredient->unit }}
                                            @else
                                                {{ $ingredient->note }}
                                            @endif
                                        </span>
                                    </li>
                                @empty
                                    <li class="text-sm text-slate-500 dark:text-slate-400">{{ __('No ingredients listed.') }}</li>
                                @endforelse
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ __('Instructions') }}</h3>
                            <p class="mt-2 whitespace-pre-line text-sm text-slate-700 dark:text-slate-200">
                                {{ $recipe->instructions }}
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts::app>
