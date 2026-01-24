<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    use AuthorizesRequests;

    public Recipe $recipe;
    public bool $canEdit = false;

    public string $title = '';
    public string $description = '';
    public int $servings = 2;
    public ?int $prepTimeMinutes = null;
    public ?int $cookTimeMinutes = null;
    public string $instructions = '';
    public string $sourceUrl = '';
    public bool $isPublic = false;
    public array $ingredients = [];

    public function mount(Recipe $recipe): void
    {
        $this->authorize('view', $recipe);

        $this->recipe = $recipe->loadMissing('originalRecipe.user');
        $this->canEdit = Gate::allows('update', $recipe);

        if ($recipe->original_recipe_id) {
            $this->canEdit = false;
        }

        $this->title = $recipe->title;
        $this->description = $recipe->description ?? '';
        $this->servings = $recipe->servings;
        $this->prepTimeMinutes = $recipe->prep_time_minutes;
        $this->cookTimeMinutes = $recipe->cook_time_minutes;
        $this->instructions = $recipe->instructions;
        $this->sourceUrl = $recipe->source_url ?? '';
        $this->isPublic = $recipe->is_public;
        $this->ingredients = $recipe->ingredients()
            ->orderBy('id')
            ->get()
            ->map(fn ($ingredient) => [
                'name' => $ingredient->name,
                'quantity' => $ingredient->quantity,
                'unit' => $ingredient->unit ?? '',
                'note' => $ingredient->note ?? '',
                'price' => $ingredient->price,
            ])
            ->all();

        if ($this->ingredients === []) {
            $this->ingredients = [$this->blankIngredient()];
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'servings' => ['required', 'integer', 'min:1', 'max:50'],
            'prepTimeMinutes' => ['nullable', 'integer', 'min:0'],
            'cookTimeMinutes' => ['nullable', 'integer', 'min:0'],
            'instructions' => ['required', 'string'],
            'sourceUrl' => ['nullable', 'url'],
            'isPublic' => ['boolean'],
            'ingredients' => ['array'],
            'ingredients.*.name' => ['required', 'string', 'max:120'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:30'],
            'ingredients.*.note' => ['nullable', 'string', 'max:255'],
            'ingredients.*.price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function addIngredient(): void
    {
        if (! $this->canEdit) {
            return;
        }

        $this->ingredients[] = $this->blankIngredient();
    }

    public function removeIngredient(int $index): void
    {
        if (! $this->canEdit) {
            return;
        }

        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);
    }

    public function save(): void
    {
        $this->authorize('update', $this->recipe);

        $this->ingredients = $this->normalizeIngredients();

        $validated = $this->validate();

        $this->recipe->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'servings' => $validated['servings'],
            'prep_time_minutes' => $validated['prepTimeMinutes'],
            'cook_time_minutes' => $validated['cookTimeMinutes'],
            'instructions' => $validated['instructions'],
            'source_url' => $validated['sourceUrl'] ?: null,
            'is_public' => $validated['isPublic'],
        ]);

        $ingredients = $this->applyIngredientPrices($validated['ingredients']);

        $this->recipe->ingredients()->delete();

        if (count($ingredients) > 0) {
            $this->recipe->ingredients()->createMany($ingredients);
        }

        $this->dispatch('recipe-updated');
    }

    public function duplicateRecipe(): void
    {
        $this->authorize('duplicate', $this->recipe);

        $isFromOther = $this->recipe->user_id !== Auth::id();

        $copy = $this->recipe->replicate(['is_public']);
        if ($isFromOther) {
            $copy->original_recipe_id = $this->recipe->original_recipe_id ?? $this->recipe->id;
        }
        $copy->user_id = Auth::id();
        $copy->title = $this->recipe->title;
        $copy->is_public = $isFromOther ? true : $this->recipe->is_public;
        $copy->save();

        $copy->ingredients()->createMany(
            $this->recipe->ingredients()
                ->get()
                ->map(fn ($ingredient) => $ingredient->only(['name', 'quantity', 'unit', 'note', 'price']))
                ->all(),
        );

        $this->redirectRoute('recipes.edit', ['recipe' => $copy->id], navigate: true);
    }

    public function deleteRecipe(): void
    {
        $this->authorize('delete', $this->recipe);

        $this->recipe->delete();

        $this->redirectRoute('recipes.index', navigate: true);
    }

    /**
     * @return array<int, array{name: string, quantity: float|null, unit: string|null, note: string|null, price: float|null}>
     */
    protected function normalizeIngredients(): array
    {
        return array_values(array_filter($this->ingredients, function (array $ingredient): bool {
            return trim((string) ($ingredient['name'] ?? '')) !== ''
                || trim((string) ($ingredient['quantity'] ?? '')) !== ''
                || trim((string) ($ingredient['unit'] ?? '')) !== ''
                || trim((string) ($ingredient['note'] ?? '')) !== '';
        }));
    }

    /**
     * @return array{name: string, quantity: null, unit: string, note: string, price: null}
     */
    protected function blankIngredient(): array
    {
        return [
            'name' => '',
            'quantity' => null,
            'unit' => '',
            'note' => '',
            'price' => null,
        ];
    }

    /**
     * @param  array<int, array{name: string, quantity: float|null, unit: string|null, note: string|null, price: float|null}>  $ingredients
     * @return array<int, array{name: string, quantity: float|null, unit: string|null, note: string|null, price: float}>
     */
    protected function applyIngredientPrices(array $ingredients): array
    {
        $userId = Auth::id();

        return collect($ingredients)
            ->map(function (array $ingredient) use ($userId): array {
                $name = trim((string) ($ingredient['name'] ?? ''));
                $price = $ingredient['price'];

                if ($price === null || $price === '') {
                    if ($name !== '') {
                        $price = Ingredient::query()
                            ->whereHas('recipe', function ($query) use ($userId): void {
                                $query->where('user_id', $userId);
                            })
                            ->where('name', $name)
                            ->orderByDesc('id')
                            ->value('price');
                    }

                    $price = $price ?? 0;
                }

                $ingredient['price'] = $price;

                return $ingredient;
            })
            ->all();
    }
};
?>

<section class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $recipe->title }}</h1>
            <p class="text-sm text-slate-600">
                @if ($recipe->originalRecipe)
                    {{ __('Viewing a copied recipe.') }}
                @else
                    {{ $canEdit ? __('Edit your recipe details.') : __('Viewing a public recipe.') }}
                @endif
            </p>
            @if ($recipe->originalRecipe)
                <p class="text-xs text-slate-500">
                    {{ __('Original by') }} {{ $recipe->originalRecipe->user?->name ?? __('Unknown') }}
                </p>
            @endif
            <p class="text-sm text-slate-600">
                {{ __('Approx.') }} ${{ number_format($recipe->approximate_price, 2) }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button variant="secondary" :href="route('recipes.index')" wire:navigate>
                {{ __('Back to recipes') }}
            </x-ui.button>

            @if ($canEdit)
                <x-ui.button size="sm" variant="primary" type="button" wire:click="duplicateRecipe">
                    {{ __('Save as copy') }}
                </x-ui.button>
                <x-ui.button size="sm" variant="danger" type="button" wire:click="deleteRecipe">
                    {{ __('Delete') }}
                </x-ui.button>
            @endif
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card class="p-6 space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.input wire:model="title" name="title" :label="__('Title')" required :disabled="! $canEdit" />
                <x-ui.input wire:model="sourceUrl" name="sourceUrl" :label="__('Source URL')" type="url" :disabled="! $canEdit" />
            </div>

            <x-ui.textarea wire:model="description" name="description" :label="__('Description')" rows="3" :disabled="! $canEdit" />

            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.input wire:model="servings" name="servings" :label="__('Servings')" type="number" min="1" max="50" :disabled="! $canEdit" />
                <x-ui.input wire:model="prepTimeMinutes" name="prepTimeMinutes" :label="__('Prep time (minutes)')" type="number" min="0" :disabled="! $canEdit" />
                <x-ui.input wire:model="cookTimeMinutes" name="cookTimeMinutes" :label="__('Cook time (minutes)')" type="number" min="0" :disabled="! $canEdit" />
            </div>

            <x-ui.textarea wire:model="instructions" name="instructions" :label="__('Instructions')" rows="6" required :disabled="! $canEdit" />

            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input
                    type="checkbox"
                    wire:model="isPublic"
                    class="h-4 w-4 rounded border-slate-300 text-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                    @disabled(! $canEdit)
                />
                {{ __('Make recipe public') }}
            </label>
        </x-ui.card>

        <x-ui.card class="p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Ingredients') }}</h2>
                @if ($canEdit)
                    <x-ui.button size="sm" variant="primary" type="button" wire:click="addIngredient">
                        {{ __('Add ingredient') }}
                    </x-ui.button>
                @endif
            </div>

            <div class="grid gap-3">
                @foreach ($ingredients as $index => $ingredient)
                    <x-ui.card class="p-4 space-y-3" wire:key="ingredient-{{ $index }}">
                        <div class="grid gap-3 md:grid-cols-7">
                            <div class="md:col-span-2">
                                <x-ui.input
                                    wire:model="ingredients.{{ $index }}.name"
                                    name="ingredients.{{ $index }}.name"
                                    error="ingredients.{{ $index }}.name"
                                    :label="__('Name')"
                                    :disabled="! $canEdit"
                                />
                            </div>
                            <div>
                                <x-ui.input
                                    wire:model="ingredients.{{ $index }}.quantity"
                                    name="ingredients.{{ $index }}.quantity"
                                    error="ingredients.{{ $index }}.quantity"
                                    :label="__('Qty')"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :disabled="! $canEdit"
                                />
                            </div>
                            <div>
                                <x-ui.input
                                    wire:model="ingredients.{{ $index }}.unit"
                                    name="ingredients.{{ $index }}.unit"
                                    error="ingredients.{{ $index }}.unit"
                                    :label="__('Unit')"
                                    :disabled="! $canEdit"
                                />
                            </div>
                            <div>
                                <x-ui.input
                                    wire:model="ingredients.{{ $index }}.price"
                                    name="ingredients.{{ $index }}.price"
                                    error="ingredients.{{ $index }}.price"
                                    :label="__('Price')"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :disabled="! $canEdit"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <x-ui.input
                                    wire:model="ingredients.{{ $index }}.note"
                                    name="ingredients.{{ $index }}.note"
                                    error="ingredients.{{ $index }}.note"
                                    :label="__('Note')"
                                    :disabled="! $canEdit"
                                />
                            </div>
                        </div>

                        @if ($canEdit)
                            <div class="flex items-center justify-end">
                                <x-ui.button size="sm" variant="danger" type="button" wire:click="removeIngredient({{ $index }})">
                                    {{ __('Remove') }}
                                </x-ui.button>
                            </div>
                        @endif
                    </x-ui.card>
                @endforeach
            </div>
        </x-ui.card>

        @if ($canEdit)
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.button variant="primary" type="submit">
                    {{ __('Save Changes') }}
                </x-ui.button>

                <x-action-message on="recipe-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        @endif
    </form>
</section>
