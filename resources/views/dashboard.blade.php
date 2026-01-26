<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Dashboard') }}</h1>
            <p class="text-sm text-slate-600">{{ __('Quick access to your meal planning tools.') }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-5 !bg-rose-50/60 !border-rose-100/60">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Top recipes') }}</h2>
                        <p class="text-xs text-slate-500">{{ $monthLabel }}</p>
                    </div>
                    <div class="grid gap-2 text-sm text-slate-700">
                        @forelse ($topRecipes as $entry)
                            <div class="flex items-center justify-between gap-2">
                                <a class="text-slate-900 hover:text-green-700" href="{{ $entry->recipe_id ? route('recipes.edit', $entry->recipe_id) : route('recipes.index') }}">
                                    {{ $entry->recipe?->title ?? __('Recipe') }}
                                </a>
                                <span class="text-xs text-slate-500">{{ $entry->uses }} {{ __('uses') }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No recipes planned last month.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 !bg-rose-50/60 !border-rose-100/60">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Top ingredients') }}</h2>
                        <p class="text-xs text-slate-500">{{ $monthLabel }}</p>
                    </div>
                    <div class="grid gap-2 text-sm text-slate-700">
                        @forelse ($topIngredients as $ingredient)
                            <div class="flex items-center justify-between gap-2">
                                <a class="text-slate-900 hover:text-green-700" href="{{ route('recipes.index', ['ingredient' => $ingredient->name]) }}">
                                    {{ $ingredient->name }}
                                </a>
                                <span class="text-xs text-slate-500">
                                    @if ($ingredient->display_quantity)
                                        {{ $ingredient->display_quantity }} {{ $ingredient->unit }}
                                    @else
                                        {{ $ingredient->uses }} {{ __('uses') }}
                                    @endif
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No ingredients used last month.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 !bg-rose-50/60 !border-rose-100/60">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Shopping total') }}</h2>
                        <p class="text-xs text-slate-500">{{ $monthLabel }}</p>
                    </div>
                    <div class="text-2xl font-semibold text-slate-900">
                        {{ __('Approx.') }} {{ $currencySymbol }}{{ $displayTotal === '' ? '0' : $displayTotal }}
                    </div>
                    <p class="text-sm text-slate-600">{{ __('From generated shopping lists.') }}</p>
                    <x-ui.button size="sm" variant="secondary" :href="route('shopping-list.index')">
                        {{ __('View list') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-5 md:col-span-3 !bg-emerald-50/60 !border-emerald-100/60">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Community favorites') }}</h2>
                        <p class="text-xs text-slate-500">{{ $monthLabel }}</p>
                    </div>
                    <div class="grid text-sm text-slate-700">
                        @forelse ($topCommunityRecipes as $entry)
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-2 last:border-b-0">
                                <div class="flex items-start gap-3">
                                    <div class="h-12 w-14 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        @if ($entry->recipe?->cover_thumbnail_url && $entry->recipe?->cover_image_url)
                                            <button type="button" class="block h-full w-full" onclick="document.getElementById('community-recipe-image-{{ $entry->recipe->id }}').showModal()">
                                                <img
                                                    src="{{ $entry->recipe->cover_thumbnail_url }}"
                                                    alt="{{ $entry->recipe->title }}"
                                                    class="h-full w-full object-cover"
                                                />
                                            </button>
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-lg">
                                                🥬
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <div class="group relative inline-block">
                                            <a class="text-slate-900 hover:text-green-700" href="{{ $entry->recipe_id ? route('recipes.edit', $entry->recipe_id) : route('recipes.index') }}">
                                                {{ $entry->recipe?->title ?? __('Recipe') }}
                                            </a>
                                            <div class="pointer-events-none absolute left-0 top-full z-20 mt-2 hidden w-56 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-900 shadow-sm group-hover:block">
                                                <div class="font-semibold text-green-950">{{ __('Ingredients') }}</div>
                                                <ul class="mt-1 list-disc pl-4 text-green-900">
                                                    @forelse ($entry->recipe?->ingredients ?? [] as $ingredient)
                                                        <li>{{ $ingredient->name }}</li>
                                                    @empty
                                                        <li>{{ __('No ingredients.') }}</li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                        <span class="text-xs text-slate-500">
                                            {{ $entry->recipe?->user?->name ?? __('Unknown') }}
                                        </span>
                                    </div>
                                </div>
                                @if ($entry->recipe?->cover_image_url)
                                    <dialog id="community-recipe-image-{{ $entry->recipe->id }}" class="m-auto w-full max-w-4xl rounded-2xl bg-transparent p-0 backdrop:bg-black/40">
                                        <div class="relative max-h-[85vh] w-full">
                                            <img
                                                src="{{ $entry->recipe->cover_image_url }}"
                                                alt="{{ $entry->recipe->title }}"
                                                class="h-full w-full rounded-2xl object-contain shadow-lg"
                                            />
                                            <form method="dialog">
                                                <button
                                                    type="submit"
                                                    class="absolute -top-4 -right-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow hover:text-slate-900"
                                                    aria-label="{{ __('Close') }}"
                                                >
                                                    ✕
                                                </button>
                                            </form>
                                        </div>
                                    </dialog>
                                @endif
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-500">{{ $entry->uses }} {{ __('uses') }}</span>
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-500" data-test="community-saves-{{ $entry->recipe_id }}">
                                        <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path d="M5 3a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16l-5-3-5 3V3z" />
                                        </svg>
                                        {{ $entry->recipe?->saves_count ?? 0 }}
                                    </span>
                                    @if ($entry->recipe)
                                        @if ($ownedOriginals->has($entry->recipe_id))
                                            <a class="text-xs font-medium text-slate-500 hover:text-green-700" href="{{ route('recipes.edit', $ownedOriginals->get($entry->recipe_id)) }}">
                                                {{ __('Already in my book') }}
                                            </a>
                                        @else
                                        <form method="POST" action="{{ route('recipes.add-to-library', $entry->recipe) }}">
                                            @csrf
                                            <x-ui.button size="sm" variant="success" type="submit">
                                                {{ __('Add to my recipe book') }}
                                            </x-ui.button>
                                        </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No public recipes yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-ui.card class="p-5 !bg-amber-50/60 !border-amber-100/60">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Top users by saves') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('All-time across their recipes') }}</p>
                    </div>
                    <div class="grid gap-3 text-sm text-slate-700">
                        @forelse ($topSavedUsers as $user)
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar
                                        :name="$user->name"
                                        :src="$user->avatar_url"
                                        :initials="$user->initials()"
                                        size="sm"
                                    />
                                    <span class="text-slate-900">{{ $user->name }}</span>
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs text-slate-500" data-test="user-saves-{{ $user->id }}">
                                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M5 3a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16l-5-3-5 3V3z" />
                                    </svg>
                                    {{ $user->saves_count }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No saves yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5 !bg-amber-50/60 !border-amber-100/60">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Top recipes of all time') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Based on total saves') }}</p>
                    </div>
                    <div class="grid gap-3 text-sm text-slate-700">
                        @forelse ($topSavedRecipes as $recipe)
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-14 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        @if ($recipe->cover_thumbnail_url && $recipe->cover_image_url)
                                            <button type="button" class="block h-full w-full" onclick="document.getElementById('top-recipe-image-{{ $recipe->id }}').showModal()">
                                                <img
                                                    src="{{ $recipe->cover_thumbnail_url }}"
                                                    alt="{{ $recipe->title }}"
                                                    class="h-full w-full object-cover"
                                                />
                                            </button>
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-lg">
                                                🥕
                                            </div>
                                        @endif
                                    </div>
                                    @if ($recipe->cover_image_url)
                                        <dialog id="top-recipe-image-{{ $recipe->id }}" class="m-auto w-full max-w-4xl rounded-2xl bg-transparent p-0 backdrop:bg-black/40">
                                            <div class="relative max-h-[85vh] w-full">
                                                <img
                                                    src="{{ $recipe->cover_image_url }}"
                                                    alt="{{ $recipe->title }}"
                                                    class="h-full w-full rounded-2xl object-contain shadow-lg"
                                                />
                                                <form method="dialog">
                                                    <button
                                                        type="submit"
                                                        class="absolute -top-4 -right-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow hover:text-slate-900"
                                                        aria-label="{{ __('Close') }}"
                                                    >
                                                        ✕
                                                    </button>
                                                </form>
                                            </div>
                                        </dialog>
                                    @endif
                                    <a class="text-slate-900 hover:text-green-700" href="{{ $recipe->id ? route('recipes.edit', $recipe->id) : route('recipes.index') }}">
                                        {{ $recipe->title }}
                                    </a>
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs text-slate-500" data-test="recipe-saves-{{ $recipe->id }}">
                                    <svg class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M5 3a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16l-5-3-5 3V3z" />
                                    </svg>
                                    {{ $recipe->saves_count ?? 0 }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No saved recipes yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts::app>
