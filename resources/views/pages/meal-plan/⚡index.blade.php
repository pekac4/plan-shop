<?php

use App\Models\MealPlanEntry;
use App\Models\Recipe;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    use AuthorizesRequests;

    public string $weekStart = '';
    public string $formDate = '';
    public string $formMeal = 'dinner';
    public ?int $formRecipeId = null;
    public string $formCustomTitle = '';
    public int $formServings = 1;
    public ?string $editingSlot = null;

    public function mount(): void
    {
        $requestedWeek = request()->query('week');
        $start = $requestedWeek ? CarbonImmutable::parse($requestedWeek) : CarbonImmutable::now();

        $this->weekStart = $start->startOfWeek(CarbonImmutable::MONDAY)->toDateString();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'formDate' => ['required', 'date'],
            'formMeal' => ['required', Rule::in(MealPlanEntry::MEALS)],
            'formRecipeId' => [
                'nullable',
                'integer',
                Rule::exists('recipes', 'id')->where(function ($query) use ($userId): void {
                    $query->where('user_id', $userId)->orWhere('is_public', true);
                }),
            ],
            'formCustomTitle' => ['nullable', 'string', 'max:120'],
            'formServings' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    #[Computed]
    public function weekDays(): array
    {
        $start = CarbonImmutable::parse($this->weekStart);

        return collect(range(0, 6))
            ->map(fn (int $offset) => $start->addDays($offset))
            ->all();
    }

    #[Computed]
    public function entries(): array
    {
        $start = CarbonImmutable::parse($this->weekStart);
        $end = $start->addDays(6);

        return MealPlanEntry::query()
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('recipe')
            ->get()
            ->keyBy(fn (MealPlanEntry $entry): string => $entry->date->toDateString().'|'.$entry->meal)
            ->all();
    }

    #[Computed]
    public function recipes()
    {
        $userId = Auth::id();

        return Recipe::query()
            ->where(function ($query) use ($userId): void {
                $query->where('user_id', $userId)->orWhere('is_public', true);
            })
            ->orderBy('title')
            ->get(['id', 'title', 'user_id', 'is_public']);
    }

    public function previousWeek(): void
    {
        $this->weekStart = CarbonImmutable::parse($this->weekStart)->subWeek()->toDateString();
        $this->redirectRoute('meal-plan.index', ['week' => $this->weekStart], navigate: true);
    }

    public function nextWeek(): void
    {
        $this->weekStart = CarbonImmutable::parse($this->weekStart)->addWeek()->toDateString();
        $this->redirectRoute('meal-plan.index', ['week' => $this->weekStart], navigate: true);
    }

    public function startEditingToday(): void
    {
        $date = CarbonImmutable::now()->startOfDay()->toDateString();

        $this->startEditing($date, 'dinner');
    }

    public function startEditing(string $date, string $meal): void
    {
        $key = $date.'|'.$meal;
        $entry = $this->entries[$key] ?? null;

        $this->editingSlot = $key;
        $this->formDate = $date;
        $this->formMeal = $meal;
        $this->formServings = $entry?->servings ?? 1;
        $this->formRecipeId = $entry?->recipe_id;
        $this->formCustomTitle = $entry?->custom_title ?? '';
    }

    public function cancelEditing(): void
    {
        $this->resetForm();
    }

    public function saveEntry(): void
    {
        $validated = $this->validate();

        $hasRecipe = $validated['formRecipeId'] !== null;
        $customTitle = trim($validated['formCustomTitle']);

        if ($hasRecipe && $customTitle !== '') {
            throw ValidationException::withMessages([
                'formCustomTitle' => __('Choose a recipe or enter a custom meal, not both.'),
            ]);
        }

        if (! $hasRecipe && $customTitle === '') {
            throw ValidationException::withMessages([
                'formCustomTitle' => __('Select a recipe or enter a custom meal.'),
            ]);
        }

        $user = Auth::user();

        $existing = MealPlanEntry::query()
            ->where('user_id', $user->id)
            ->where('date', $validated['formDate'])
            ->where('meal', $validated['formMeal'])
            ->first();

        if ($existing) {
            $this->authorize('update', $existing);
        } else {
            $this->authorize('create', MealPlanEntry::class);
        }

        $entry = $existing ?? new MealPlanEntry(['user_id' => $user->id]);
        $entry->date = $validated['formDate'];
        $entry->meal = $validated['formMeal'];
        $entry->recipe_id = $hasRecipe ? $validated['formRecipeId'] : null;
        $entry->custom_title = $hasRecipe ? null : $customTitle;
        $entry->servings = $validated['formServings'];
        $entry->save();

        $this->resetForm();
    }

    public function removeEntry(int $entryId): void
    {
        $entry = MealPlanEntry::query()->where('user_id', Auth::id())->findOrFail($entryId);

        $this->authorize('delete', $entry);

        $entry->delete();
    }

    protected function resetForm(): void
    {
        $this->editingSlot = null;
        $this->formDate = '';
        $this->formMeal = 'dinner';
        $this->formRecipeId = null;
        $this->formCustomTitle = '';
        $this->formServings = 1;
    }
};
?>

@php
    $mealTones = [
        'breakfast' => 'amber',
        'lunch' => 'sky',
        'dinner' => 'emerald',
        'snack' => 'lime',
    ];
@endphp

<section class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Meal Plan') }}</h1>
                <p class="text-sm text-slate-600">{{ __('Plan meals for the week.') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button size="sm" variant="primary" wire:click="startEditingToday" data-test="meal-add-today">
                    {{ __('Add meal') }}
                </x-ui.button>
                <x-ui.button size="sm" variant="secondary" wire:click="previousWeek">
                    {{ __('Previous week') }}
                </x-ui.button>
                <x-ui.button size="sm" variant="secondary" wire:click="nextWeek">
                    {{ __('Next week') }}
            </x-ui.button>
        </div>
    </div>

    <x-ui.card class="p-6 space-y-6">
        <div class="overflow-x-auto">
            <div class="min-w-[860px]">
                <div class="grid grid-cols-8 gap-2 text-sm font-medium text-slate-600">
                    <div></div>
                    @foreach ($this->weekDays as $day)
                        <div class="text-center">
                            <div>{{ $day->format('D') }}</div>
                            <div class="text-xs text-slate-400">{{ $day->format('M j') }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 grid gap-3">
                    @foreach (MealPlanEntry::MEALS as $meal)
                        <div class="grid grid-cols-8 gap-2">
                            <div class="flex items-center justify-end text-sm font-medium text-slate-600">
                                {{ ucfirst($meal) }}
                            </div>

                            @foreach ($this->weekDays as $day)
                                @php($slotKey = $day->toDateString().'|'.$meal)
                                @php($entry = $this->entries[$slotKey] ?? null)

                                <div class="rounded-xl border border-slate-200 bg-white p-3 text-sm" wire:key="slot-{{ $slotKey }}">
                                    @if ($this->editingSlot === $slotKey)
                                        <div class="grid gap-3">
                                            <div class="grid gap-1">
                                                <label class="text-sm font-medium text-slate-700">{{ __('Recipe') }}</label>
                                                <select
                                                    id="formRecipeId-{{ $slotKey }}"
                                                    name="formRecipeId"
                                                    wire:model="formRecipeId"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                                                    wire:key="recipe-select-{{ $slotKey }}"
                                                >
                                                    <option value="">{{ __('Select a recipe') }}</option>
                                                    @foreach ($this->recipes as $recipe)
                                                        <option value="{{ $recipe->id }}">
                                                            {{ $recipe->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formRecipeId')
                                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <x-ui.input wire:model="formCustomTitle" name="formCustomTitle" :label="__('Custom meal')" placeholder="{{ __('E.g. Sandwiches') }}" />

                                            <x-ui.input wire:model="formServings" name="formServings" :label="__('Servings')" type="number" min="1" max="50" />

                                            <div class="flex items-center gap-2">
                                                <x-ui.button size="sm" variant="primary" wire:click="saveEntry" data-test="meal-save">
                                                    {{ __('Save') }}
                                                </x-ui.button>
                                                <x-ui.button size="sm" variant="secondary" wire:click="cancelEditing" data-test="meal-cancel">
                                                    {{ __('Cancel') }}
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    @elseif ($entry)
                                        <div class="grid gap-2">
                                            <div class="font-medium text-slate-900">
                                                {{ $entry->recipe?->title ?? $entry->custom_title }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $entry->recipe_id ? __('Recipe') : __('Custom') }}
                                                · {{ $entry->servings }} {{ __('servings') }}
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button size="sm" variant="edit" wire:click="startEditing('{{ $day->toDateString() }}', '{{ $meal }}')" data-test="meal-edit-{{ $slotKey }}">
                    {{ __('Edit') }}
                </x-ui.button>
                <x-ui.button size="sm" variant="danger" wire:click="removeEntry({{ $entry->id }})" data-test="meal-remove-{{ $slotKey }}">
                    {{ __('Remove') }}
                </x-ui.button>
                                            </div>
                                        </div>
                                    @else
            <x-ui.button size="sm" variant="primary" wire:click="startEditing('{{ $day->toDateString() }}', '{{ $meal }}')" data-test="meal-add-{{ $slotKey }}">
                {{ __('Add') }}
            </x-ui.button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-ui.card>
</section>
