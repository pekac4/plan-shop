<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    use AuthorizesRequests;

    public string $title = '';
    public string $description = '';
    public int $servings = 2;
    public ?int $prepTimeMinutes = null;
    public ?int $cookTimeMinutes = null;
    public string $instructions = '';
    public string $sourceUrl = '';
    public bool $isPublic = false;
    public array $ingredients = [];

    public function mount(): void
    {
        $this->ingredients = [
            $this->blankIngredient(),
        ];
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
        $this->ingredients[] = $this->blankIngredient();
    }

    public function removeIngredient(int $index): void
    {
        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);
    }

    public function save(): void
    {
        $this->authorize('create', Recipe::class);

        $this->ingredients = $this->normalizeIngredients();

        $validated = $this->validate();

        $recipe = Recipe::query()->create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'servings' => $validated['servings'],
            'prep_time_minutes' => $validated['prepTimeMinutes'],
            'cook_time_minutes' => $validated['cookTimeMinutes'],
            'instructions' => $validated['instructions'],
            'source_url' => $validated['sourceUrl'] ?: null,
            'is_public' => $validated['isPublic'],
        ]);

        if (count($validated['ingredients']) > 0) {
            $recipe->ingredients()->createMany($this->applyIngredientPrices($validated['ingredients']));
        }

        $this->redirectRoute('recipes.edit', ['recipe' => $recipe->id], navigate: true);
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
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Create Recipe') }}</h1>
            <p class="text-sm text-slate-600">{{ __('Add a new recipe and its ingredients.') }}</p>
        </div>

        <x-ui.button variant="secondary" :href="route('recipes.index')" wire:navigate>
            {{ __('Back to recipes') }}
        </x-ui.button>
    </div>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card class="p-6 space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.input wire:model="title" name="title" :label="__('Title')" required />
                <x-ui.input wire:model="sourceUrl" name="sourceUrl" :label="__('Source URL')" type="url" />
            </div>

            <x-ui.textarea wire:model="description" name="description" :label="__('Description')" rows="3" />

            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.input wire:model="servings" name="servings" :label="__('Servings')" type="number" min="1" max="50" />
                <x-ui.input wire:model="prepTimeMinutes" name="prepTimeMinutes" :label="__('Prep time (minutes)')" type="number" min="0" />
                <x-ui.input wire:model="cookTimeMinutes" name="cookTimeMinutes" :label="__('Cook time (minutes)')" type="number" min="0" />
            </div>

            <x-ui.textarea wire:model="instructions" name="instructions" :label="__('Instructions')" rows="6" required />

            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input
                    type="checkbox"
                    wire:model="isPublic"
                    class="h-4 w-4 rounded border-slate-300 text-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                />
                {{ __('Make recipe public') }}
            </label>
        </x-ui.card>

        <x-ui.card class="p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Ingredients') }}</h2>
                <x-ui.button size="sm" variant="primary" type="button" wire:click="addIngredient">
                    {{ __('Add ingredient') }}
                </x-ui.button>
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
                                />
                            </div>
                            <div>
                                <x-ui.input
                                    wire:model="ingredients.{{ $index }}.unit"
                                    name="ingredients.{{ $index }}.unit"
                                    error="ingredients.{{ $index }}.unit"
                                    :label="__('Unit')"
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
                                />
                            </div>
                            <div class="md:col-span-2">
                                <x-ui.input
                                    wire:model="ingredients.{{ $index }}.note"
                                    name="ingredients.{{ $index }}.note"
                                    error="ingredients.{{ $index }}.note"
                                    :label="__('Note')"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-ui.button size="sm" variant="danger" type="button" wire:click="removeIngredient({{ $index }})">
                                {{ __('Remove') }}
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        </x-ui.card>

        <div class="flex items-center justify-end">
            <x-ui.button variant="primary" type="submit">
                {{ __('Save Recipe') }}
            </x-ui.button>
        </div>
    </form>
</section>
