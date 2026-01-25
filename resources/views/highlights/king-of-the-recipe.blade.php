<x-layouts::app :title="__('King of the Recipe')">
    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
        <div class="space-y-1 text-center">
            <div class="flex items-center justify-center gap-2 text-amber-600">
                <flux:icon.crown class="size-6" />
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('King of the Recipe') }}</h1>
            </div>
            <p class="text-sm text-slate-600">{{ __('The chef with the most stars across all public recipes.') }}</p>
        </div>

        @if (! $leader)
            <x-ui.card class="p-6">
                <div class="flex flex-col items-center justify-center gap-3 text-center">
                    <div class="text-3xl">🥬</div>
                    <p class="text-sm text-slate-600">{{ __('No stars yet.') }}</p>
                </div>
            </x-ui.card>
        @else
            <x-ui.card class="mx-auto w-full max-w-lg overflow-hidden">
                <div class="flex flex-col gap-6 p-6 md:flex-row md:items-center">
                    <div class="flex items-center gap-4">
                        <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-amber-100 text-2xl font-semibold text-amber-700">
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
                            <p class="text-sm text-slate-500">{{ __('King of the Recipe') }}</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $leader->name }}</p>
                            <p class="text-sm text-slate-600">
                                {{ __('Total stars') }}: <span class="font-medium text-slate-800">{{ $leader->stars ?? 0 }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Top recipes') }}</h2>
                        <span class="text-xs text-slate-500">{{ __('Top 10 by stars') }}</span>
                    </div>

                    <div class="mt-4 grid gap-3">
                        @forelse ($leaderRecipes as $recipe)
                            <a
                                href="{{ route('recipes.edit', $recipe->id) }}"
                                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-amber-200 hover:bg-amber-50/40"
                            >
                                <div class="h-14 w-16 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
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
                                    <p class="text-sm font-medium text-slate-900">{{ $recipe->title }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $recipe->stars ?? 0 }} {{ __('stars') }}
                                    </p>
                                </div>
                                <flux:icon.chevron-right class="size-4 text-slate-400" />
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No recipes yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        @endif
    </div>
</x-layouts::app>
