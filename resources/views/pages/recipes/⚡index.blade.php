<?php

use App\Models\Recipe;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';
    public string $visibility = 'all';
    public string $ingredient = '';
    public string $ownership = 'all';

    public function mount(): void
    {
        $this->ingredient = (string) request()->query('ingredient', '');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingVisibility(): void
    {
        $this->resetPage();
    }

    public function updatingOwnership(): void
    {
        $this->resetPage();
    }

    public function updatingIngredient(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function recipes()
    {
        $user = Auth::user();

        $query = Recipe::query()
            ->withCount('ingredients')
            ->with(['ingredients', 'user:id,name', 'originalRecipe.user:id,name'])
            ->where('user_id', $user->id);

        if ($this->ownership === 'mine') {
            $query->whereNull('original_recipe_id');
        }

        if ($this->ownership === 'copied') {
            $query->whereNotNull('original_recipe_id')
                ->whereHas('originalRecipe', function ($builder) use ($user): void {
                    $builder->where('user_id', '!=', $user->id);
                });
        }

        if ($this->ownership !== 'copied') {
            if ($this->visibility === 'public') {
                $query->where('is_public', true);
            }

            if ($this->visibility === 'private') {
                $query->where('is_public', false)
                    ->whereNull('original_recipe_id');
            }
        }

        if (trim($this->search) !== '') {
            $query->where('title', 'like', '%'.trim($this->search).'%');
        }

        if (trim($this->ingredient) !== '') {
            $ingredient = trim($this->ingredient);
            $query->whereHas('ingredients', function ($builder) use ($ingredient): void {
                $builder->where('name', 'like', '%'.$ingredient.'%');
            });
        }

        return $query
            ->orderBy('title')
            ->paginate(10);
    }

    public function deleteRecipe(int $recipeId): void
    {
        $recipe = Recipe::query()->findOrFail($recipeId);

        $this->authorize('delete', $recipe);

        $recipe->delete();
        $this->resetPage();
    }

    public function duplicateRecipe(int $recipeId): void
    {
        $recipe = Recipe::query()->with('ingredients')->findOrFail($recipeId);

        $this->authorize('duplicate', $recipe);

        $isFromOther = $recipe->user_id !== Auth::id();

        $copy = $recipe->replicate(['is_public']);
        if ($isFromOther) {
            $copy->original_recipe_id = $recipe->original_recipe_id ?? $recipe->id;
        }
        $copy->user_id = Auth::id();
        $copy->title = $recipe->title;
        $copy->is_public = $isFromOther ? true : $recipe->is_public;
        $copy->save();

        $copy->ingredients()->createMany(
            $recipe->ingredients
                ->map(fn ($ingredient) => $ingredient->only(['name', 'quantity', 'unit', 'note', 'price']))
                ->all(),
        );

        $this->redirectRoute('recipes.edit', ['recipe' => $copy->id], navigate: true);
    }
};
?>

<section class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Recipes') }}</h1>
            <p class="text-sm text-slate-600">
                {{ __('Manage your recipes and ingredients.') }}
            </p>
        </div>

        <a
            href="{{ route('recipes.create') }}"
            wire:navigate
            data-test="recipes-create"
            class="inline-flex items-center justify-center rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
        >
            {{ __('Create Recipe') }}
        </a>
    </div>

    <x-ui.card class="p-6 space-y-4">
        <div class="flex justify-end">
            <a
                href="{{ route('recipes.create') }}"
                wire:navigate
                data-test="recipes-create-secondary"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
            >
                {{ __('Create Recipe') }}
            </a>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <x-ui.input wire:model.live="search" name="search" :label="__('Search')" placeholder="{{ __('Search by title') }}" />

            <div class="grid gap-1">
                <label class="text-sm font-medium text-slate-700" for="visibility">{{ __('Visibility') }}</label>
                <select
                    id="visibility"
                    name="visibility"
                    wire:model.live="visibility"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                >
                    <option value="all">{{ __('All') }}</option>
                    <option value="public">{{ __('Public') }}</option>
                    <option value="private">{{ __('Private') }}</option>
                </select>
                @error('visibility')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-1">
                <label class="text-sm font-medium text-slate-700" for="ownership">{{ __('Ownership') }}</label>
                <select
                    id="ownership"
                    name="ownership"
                    wire:model.live="ownership"
                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                >
                    <option value="all">{{ __('All') }}</option>
                    <option value="mine">{{ __('Mine') }}</option>
                    <option value="copied">{{ __('From others') }}</option>
                </select>
                @error('ownership')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="hidden md:block"></div>
        </div>

        <div class="grid gap-4">
            @forelse ($this->recipes as $recipe)
                <x-ui.card class="p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="space-y-2">
                            <div>
                                <a class="text-lg font-semibold text-slate-900 hover:text-green-700" href="{{ route('recipes.edit', $recipe) }}" wire:navigate>
                                    {{ $recipe->title }}
                                </a>
                                <p class="text-sm text-slate-600">
                                    {{ $recipe->servings }} {{ __('servings') }} · {{ $recipe->ingredients_count }} {{ __('ingredients') }}
                                </p>
                                @if ($recipe->originalRecipe)
                                    <p class="text-xs text-slate-500">
                                        {{ __('Original by') }} {{ $recipe->originalRecipe->user?->name ?? __('Unknown') }}
                                    </p>
                                @elseif ($recipe->user_id !== Auth::id())
                                    <p class="text-xs text-slate-500">
                                        {{ __('By') }} {{ $recipe->user?->name ?? __('Unknown') }}
                                    </p>
                                @endif
                                <p class="text-sm text-slate-600">
                                    {{ __('Approx.') }} ${{ number_format($recipe->approximate_price, 2) }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($recipe->is_public)
                                    <x-ui.badge tone="emerald">{{ __('Public') }}</x-ui.badge>
                                @else
                                    <x-ui.badge tone="amber">{{ __('Private') }}</x-ui.badge>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @can('update', $recipe)
                                <x-ui.button size="sm" variant="secondary" :href="route('recipes.edit', $recipe)" wire:navigate>
                                    {{ __('Edit') }}
                                </x-ui.button>
                            @else
                                <x-ui.button size="sm" variant="secondary" :href="route('recipes.edit', $recipe)" wire:navigate>
                                    {{ __('View') }}
                                </x-ui.button>
                            @endcan

                            @can('duplicate', $recipe)
                                <x-ui.button size="sm" variant="secondary" wire:click="duplicateRecipe({{ $recipe->id }})">
                                    {{ __('Save as copy') }}
                                </x-ui.button>
                            @endcan

                            @can('delete', $recipe)
                                <x-ui.button size="sm" variant="danger" wire:click="deleteRecipe({{ $recipe->id }})">
                                    {{ __('Delete') }}
                                </x-ui.button>
                            @endcan
                        </div>
                    </div>

                    @if ($recipe->description)
                        <p class="text-sm text-slate-600">
                            {{ $recipe->description }}
                        </p>
                    @endif
                </x-ui.card>
            @empty
                <x-ui.card class="p-10 text-center">
                    <div class="space-y-3">
                        <div class="text-3xl" aria-hidden="true">🥬</div>
                        <p class="text-sm text-slate-600">{{ __('No recipes yet.') }}</p>
                        <x-ui.button variant="primary" :href="route('recipes.create')" wire:navigate data-test="recipes-create-empty">
                            {{ __('Create Recipe') }}
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endforelse
        </div>

        <div>
            {{ $this->recipes->links() }}
        </div>
    </x-ui.card>
</section>
