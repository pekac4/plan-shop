<x-layouts::app :title="__('Dashboard')">
    @php
        $monthStart = \Carbon\CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $topRecipes = \App\Models\MealPlanEntry::query()
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereNotNull('recipe_id')
            ->select('recipe_id')
            ->selectRaw('count(*) as uses')
            ->groupBy('recipe_id')
            ->orderByDesc('uses')
            ->with('recipe:id,title')
            ->limit(5)
            ->get();

        $topIngredients = \App\Models\Ingredient::query()
            ->select('ingredients.name', 'ingredients.unit')
            ->selectRaw('count(*) as uses')
            ->selectRaw('sum(coalesce(ingredients.quantity, 1) * meal_plan_entries.servings) as total_quantity')
            ->join('recipes', 'recipes.id', '=', 'ingredients.recipe_id')
            ->join('meal_plan_entries', 'meal_plan_entries.recipe_id', '=', 'recipes.id')
            ->where('meal_plan_entries.user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereBetween('meal_plan_entries.date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->groupBy('ingredients.name', 'ingredients.unit')
            ->orderByDesc('uses')
            ->limit(5)
            ->get();

        $shoppingTotal = (float) \App\Models\ShoppingListItem::query()
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereBetween('range_start', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('price');

        $customTotal = (float) \App\Models\ShoppingListCustomItem::query()
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereBetween('range_start', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('price');

        $monthlyTotal = $shoppingTotal + $customTotal;
        $displayTotal = rtrim(rtrim(number_format($monthlyTotal, 2, '.', ''), '0'), '.');
        $monthLabel = $monthStart->format('F Y');

        $ownedOriginals = \App\Models\Recipe::query()
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereNotNull('original_recipe_id')
            ->get(['id', 'original_recipe_id'])
            ->groupBy('original_recipe_id');

        $topCommunityRecipes = \App\Models\MealPlanEntry::query()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereNotNull('recipe_id')
            ->whereHas('recipe', function ($query): void {
                $query->where('is_public', true)
                    ->where('user_id', '!=', \Illuminate\Support\Facades\Auth::id());
            })
            ->select('recipe_id')
            ->selectRaw('count(*) as uses')
            ->groupBy('recipe_id')
            ->orderByDesc('uses')
            ->with([
                'recipe' => function ($query): void {
                    $query->select('id', 'title', 'is_public', 'user_id', 'cover_image_path', 'cover_thumbnail_path')
                        ->selectSub(function ($subQuery): void {
                            $subQuery->from('recipes as copies')
                                ->selectRaw('count(distinct copies.user_id)')
                                ->whereColumn('copies.original_recipe_id', 'recipes.id')
                                ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
                        }, 'stars')
                        ->with('ingredients:id,recipe_id,name');
                },
                'recipe.user:id,name',
            ])
            ->limit(5)
            ->get();

        $topStarUsers = \App\Models\User::query()
            ->select('users.id', 'users.name', 'users.avatar_path')
            ->selectRaw('count(copies.id) as stars')
            ->join('recipes as originals', 'originals.user_id', '=', 'users.id')
            ->leftJoin('recipes as copies', function ($join): void {
                $join->on('copies.original_recipe_id', '=', 'originals.id')
                    ->whereColumn('copies.user_id', '!=', 'originals.user_id');
            })
            ->whereNull('originals.original_recipe_id')
            ->where('originals.is_public', true)
            ->groupBy('users.id', 'users.name', 'users.avatar_path')
            ->orderByDesc('stars')
            ->limit(5)
            ->get();

        $topStarRecipes = \App\Models\Recipe::query()
            ->whereNull('original_recipe_id')
            ->where('is_public', true)
            ->select('id', 'title', 'cover_image_path', 'cover_thumbnail_path')
            ->selectSub(function ($subQuery): void {
                $subQuery->from('recipes as copies')
                    ->selectRaw('count(distinct copies.user_id)')
                    ->whereColumn('copies.original_recipe_id', 'recipes.id')
                    ->whereColumn('copies.user_id', '!=', 'recipes.user_id');
            }, 'stars')
            ->orderByDesc('stars')
            ->limit(5)
            ->get();
    @endphp

    <div class="space-y-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Dashboard') }}</h1>
            <p class="text-sm text-slate-600">{{ __('Quick access to your meal planning tools.') }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-5">
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

            <x-ui.card class="p-5">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Top ingredients') }}</h2>
                        <p class="text-xs text-slate-500">{{ $monthLabel }}</p>
                    </div>
                    <div class="grid gap-2 text-sm text-slate-700">
                        @forelse ($topIngredients as $ingredient)
                            @php
                                $quantity = $ingredient->total_quantity ? rtrim(rtrim(number_format((float) $ingredient->total_quantity, 2, '.', ''), '0'), '.') : null;
                            @endphp
                            <div class="flex items-center justify-between gap-2">
                                <a class="text-slate-900 hover:text-green-700" href="{{ route('recipes.index', ['ingredient' => $ingredient->name]) }}">
                                    {{ $ingredient->name }}
                                </a>
                                <span class="text-xs text-slate-500">
                                    @if ($quantity)
                                        {{ $quantity }} {{ $ingredient->unit }}
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

            <x-ui.card class="p-5">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Shopping total') }}</h2>
                        <p class="text-xs text-slate-500">{{ $monthLabel }}</p>
                    </div>
                    <div class="text-2xl font-semibold text-slate-900">
                        {{ __('Approx.') }} ${{ $displayTotal === '' ? '0' : $displayTotal }}
                    </div>
                    <p class="text-sm text-slate-600">{{ __('From generated shopping lists.') }}</p>
                    <x-ui.button size="sm" variant="secondary" :href="route('shopping-list.index')">
                        {{ __('View list') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-5 md:col-span-3">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Community favorites') }}</h2>
                        <p class="text-xs text-slate-500">{{ $monthLabel }}</p>
                    </div>
                    <div class="grid text-sm text-slate-700">
                        @forelse ($topCommunityRecipes as $entry)
                            @php
                                $alreadyOwned = $ownedOriginals->has($entry->recipe_id);
                                $ownedRecipeId = $alreadyOwned ? $ownedOriginals->get($entry->recipe_id)->first()?->id : null;
                            @endphp
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-2 last:border-b-0">
                                <div class="flex items-start gap-3">
                                    <div class="h-12 w-14 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        @if ($entry->recipe?->cover_thumbnail_url && $entry->recipe?->cover_image_url)
                                            <a href="#community-recipe-image-{{ $entry->recipe->id }}" class="block h-full w-full">
                                                <img
                                                    src="{{ $entry->recipe->cover_thumbnail_url }}"
                                                    alt="{{ $entry->recipe->title }}"
                                                    class="h-full w-full object-cover"
                                                />
                                            </a>
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
                                    <div
                                        id="community-recipe-image-{{ $entry->recipe->id }}"
                                        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-6 target:flex"
                                    >
                                        <a class="absolute inset-0" href="#"></a>
                                        <div class="relative max-h-[85vh] w-full max-w-4xl">
                                            <img
                                                src="{{ $entry->recipe->cover_image_url }}"
                                                alt="{{ $entry->recipe->title }}"
                                                class="h-full w-full rounded-2xl object-contain shadow-lg"
                                            />
                                            <a
                                                href="#"
                                                class="absolute -top-4 -right-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow hover:text-slate-900"
                                                aria-label="{{ __('Close') }}"
                                            >
                                                ✕
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-500">{{ $entry->uses }} {{ __('uses') }}</span>
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-500" data-test="community-stars-{{ $entry->recipe_id }}">
                                        <svg class="h-4 w-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M9.05 2.927c.3-.921 1.603-.921 1.902 0l1.12 3.44a1 1 0 0 0 .95.69h3.62c.969 0 1.371 1.24.588 1.81l-2.93 2.128a1 1 0 0 0-.364 1.118l1.12 3.44c.3.922-.755 1.688-1.54 1.118l-2.93-2.127a1 1 0 0 0-1.175 0l-2.93 2.127c-.784.57-1.838-.196-1.539-1.118l1.12-3.44a1 1 0 0 0-.364-1.118L2.72 8.867c-.783-.57-.38-1.81.588-1.81h3.62a1 1 0 0 0 .95-.69l1.12-3.44Z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $entry->recipe?->stars ?? 0 }}
                                    </span>
                                    @if ($entry->recipe)
                                        @if ($alreadyOwned && $ownedRecipeId)
                                            <a class="text-xs font-medium text-slate-500 hover:text-green-700" href="{{ route('recipes.edit', $ownedRecipeId) }}">
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
            <x-ui.card class="p-5">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Top users by stars') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('All-time across their recipes') }}</p>
                    </div>
                    <div class="grid gap-3 text-sm text-slate-700">
                        @forelse ($topStarUsers as $user)
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
                                <span class="inline-flex items-center gap-1 text-xs text-slate-500" data-test="user-stars-{{ $user->id }}">
                                    <svg class="h-4 w-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M9.05 2.927c.3-.921 1.603-.921 1.902 0l1.12 3.44a1 1 0 0 0 .95.69h3.62c.969 0 1.371 1.24.588 1.81l-2.93 2.128a1 1 0 0 0-.364 1.118l1.12 3.44c.3.922-.755 1.688-1.54 1.118l-2.93-2.127a1 1 0 0 0-1.175 0l-2.93 2.127c-.784.57-1.838-.196-1.539-1.118l1.12-3.44a1 1 0 0 0-.364-1.118L2.72 8.867c-.783-.57-.38-1.81.588-1.81h3.62a1 1 0 0 0 .95-.69l1.12-3.44Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $user->stars }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No stars yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Top recipes of all time') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Based on total stars') }}</p>
                    </div>
                    <div class="grid gap-3 text-sm text-slate-700">
                        @forelse ($topStarRecipes as $recipe)
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-14 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                        @if ($recipe->cover_thumbnail_url && $recipe->cover_image_url)
                                            <a href="#top-recipe-image-{{ $recipe->id }}" class="block h-full w-full">
                                                <img
                                                    src="{{ $recipe->cover_thumbnail_url }}"
                                                    alt="{{ $recipe->title }}"
                                                    class="h-full w-full object-cover"
                                                />
                                            </a>
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-lg">
                                                🥕
                                            </div>
                                        @endif
                                    </div>
                                    @if ($recipe->cover_image_url)
                                        <div
                                            id="top-recipe-image-{{ $recipe->id }}"
                                            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-6 target:flex"
                                        >
                                            <a class="absolute inset-0" href="#"></a>
                                            <div class="relative max-h-[85vh] w-full max-w-4xl">
                                                <img
                                                    src="{{ $recipe->cover_image_url }}"
                                                    alt="{{ $recipe->title }}"
                                                    class="h-full w-full rounded-2xl object-contain shadow-lg"
                                                />
                                                <a
                                                    href="#"
                                                    class="absolute -top-4 -right-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-700 shadow hover:text-slate-900"
                                                    aria-label="{{ __('Close') }}"
                                                >
                                                    ✕
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    <a class="text-slate-900 hover:text-green-700" href="{{ $recipe->id ? route('recipes.edit', $recipe->id) : route('recipes.index') }}">
                                        {{ $recipe->title }}
                                    </a>
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs text-slate-500" data-test="recipe-stars-{{ $recipe->id }}">
                                    <svg class="h-4 w-4 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M9.05 2.927c.3-.921 1.603-.921 1.902 0l1.12 3.44a1 1 0 0 0 .95.69h3.62c.969 0 1.371 1.24.588 1.81l-2.93 2.128a1 1 0 0 0-.364 1.118l1.12 3.44c.3.922-.755 1.688-1.54 1.118l-2.93-2.127a1 1 0 0 0-1.175 0l-2.93 2.127c-.784.57-1.838-.196-1.539-1.118l1.12-3.44a1 1 0 0 0-.364-1.118L2.72 8.867c-.783-.57-.38-1.81.588-1.81h3.62a1 1 0 0 0 .95-.69l1.12-3.44Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $recipe->stars ?? 0 }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No starred recipes yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts::app>
