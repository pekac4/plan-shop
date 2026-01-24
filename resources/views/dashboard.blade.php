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
            ->with('recipe:id,title,is_public,user_id')
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
                                <a class="text-slate-900 hover:text-green-700" href="{{ $entry->recipe ? route('recipes.edit', $entry->recipe) : route('recipes.index') }}" wire:navigate>
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
                                <a class="text-slate-900 hover:text-green-700" href="{{ route('recipes.index', ['ingredient' => $ingredient->name]) }}" wire:navigate>
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
                    <x-ui.button size="sm" variant="secondary" :href="route('shopping-list.index')" wire:navigate>
                        {{ __('View list') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-5 md:col-span-2">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('Community favorites') }}</h2>
                        <p class="text-xs text-slate-500">{{ $monthLabel }}</p>
                    </div>
                    <div class="grid gap-2 text-sm text-slate-700">
                        @forelse ($topCommunityRecipes as $entry)
                            <div class="flex items-center justify-between gap-2">
                                <a class="text-slate-900 hover:text-green-700" href="{{ $entry->recipe ? route('recipes.edit', $entry->recipe) : route('recipes.index') }}" wire:navigate>
                                    {{ $entry->recipe?->title ?? __('Recipe') }}
                                </a>
                                <span class="text-xs text-slate-500">{{ $entry->uses }} {{ __('uses') }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('No public recipes used last month.') }}</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts::app>
