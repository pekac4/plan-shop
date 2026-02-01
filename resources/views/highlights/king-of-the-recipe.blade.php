<x-layouts::app :title="__('King of the Recipe')">
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
        <div class="space-y-1 text-center">
            <div class="flex items-center justify-center gap-2 text-amber-600 dark:text-amber-300">
                <flux:icon.crown class="size-6" />
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('King of the Recipe') }}</h1>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('The chef with the most stars across all public recipes.') }}</p>
        </div>

        @if (! $leader)
            <x-ui.card class="p-6">
                <div class="flex flex-col items-center justify-center gap-3 text-center">
                    <div class="text-3xl">🥬</div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('No stars yet.') }}</p>
                </div>
            </x-ui.card>
        @else
            <x-ui.card class="mx-auto w-full max-w-lg overflow-hidden">
                <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-amber-100 text-2xl font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-200">
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
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('King of the Recipe') }}</p>
                            <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $leader->name }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ __('Total stars') }}: <span class="font-medium text-slate-800 dark:text-slate-100">{{ $leader->stars ?? 0 }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Top recipes') }}</h2>
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('Top 10 by stars') }}</span>
                    </div>

                    <div class="mt-4 grid gap-3">
                        @forelse ($leaderRecipes as $recipe)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-amber-200 hover:bg-amber-50/40 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-amber-400/40 dark:hover:bg-slate-800">
                                <x-ui.recipe-image
                                    :id="'king-recipe-image-'.$recipe->id"
                                    :title="$recipe->title"
                                    :image="$recipe->cover_image_url"
                                    :thumbnail="$recipe->cover_thumbnail_url"
                                    emoji="🥬"
                                    container-class="h-14 w-16 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900"
                                    image-class="h-full w-full object-cover"
                                    placeholder-class="text-lg"
                                />
                                <div class="flex-1">
                                    <a class="text-sm font-medium text-slate-900 hover:text-amber-700 dark:text-slate-100 dark:hover:text-amber-300" href="{{ route('recipes.edit', $recipe->id) }}">
                                        {{ $recipe->title }}
                                    </a>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $recipe->stars ?? 0 }} {{ __('stars') }}
                                    </p>
                                </div>
                                <a class="inline-flex items-center text-slate-400 hover:text-amber-600 dark:text-slate-500 dark:hover:text-amber-300" href="{{ route('recipes.edit', $recipe->id) }}" aria-label="{{ __('View recipe') }}">
                                    <flux:icon.chevron-right class="size-4" />
                                </a>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('No recipes yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts::app>
