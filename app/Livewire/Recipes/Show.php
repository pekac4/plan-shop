<?php

namespace App\Livewire\Recipes;

use App\Models\Recipe;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Recipe $recipe;

    /**
     * @var array<int, array{name: string, quantity: float|null, unit: string|null, price: float|null, note: string|null}>
     */
    public array $ingredients = [];

    /**
     * @var array<int, string>
     */
    public array $instructionLines = [];

    public bool $useInstructionList = false;

    public bool $canEdit = false;

    public ?float $totalCost = null;

    public ?string $coverImageUrl = null;

    public ?string $coverThumbnailUrl = null;

    public function mount(Recipe $recipe): void
    {
        $this->authorize('view', $recipe);

        $this->recipe = $recipe->loadMissing(['ingredients', 'originalRecipe.user']);
        $this->canEdit = Gate::allows('update', $recipe) && $recipe->original_recipe_id === null;
        $this->coverImageUrl = $this->recipe->cover_image_url;
        $this->coverThumbnailUrl = $this->recipe->cover_thumbnail_url;

        $this->ingredients = $this->recipe->ingredients()
            ->orderBy('id')
            ->get()
            ->map(fn ($ingredient) => [
                'name' => $ingredient->name,
                'quantity' => $ingredient->quantity,
                'unit' => $ingredient->unit,
                'price' => $ingredient->price,
                'note' => $ingredient->note,
            ])
            ->all();

        $this->instructionLines = $this->parseInstructions($this->recipe->instructions);
        $this->useInstructionList = count($this->instructionLines) > 1;
        $this->totalCost = $this->calculateTotalCost($this->ingredients);
    }

    /**
     * @param  array<int, array{name: string, quantity: float|null, unit: string|null, price: float|null, note: string|null}>  $ingredients
     */
    protected function calculateTotalCost(array $ingredients): ?float
    {
        $hasPrice = collect($ingredients)->contains(function (array $ingredient): bool {
            return $ingredient['price'] !== null && $ingredient['price'] !== '';
        });

        if (! $hasPrice) {
            return null;
        }

        $total = collect($ingredients)->reduce(function (float $carry, array $ingredient): float {
            $price = (float) ($ingredient['price'] ?? 0);
            $quantity = $ingredient['quantity'];

            if ($quantity === null || $quantity === '') {
                return $carry + $price;
            }

            return $carry + ($price * (float) $quantity);
        }, 0.0);

        return round($total, 2);
    }

    /**
     * @return array<int, string>
     */
    protected function parseInstructions(?string $instructions): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim((string) $instructions));

        if (! is_array($lines)) {
            return [];
        }

        return collect($lines)
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.recipes.show');
    }
}
