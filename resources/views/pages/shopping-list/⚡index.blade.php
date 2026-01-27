<?php

use App\Actions\ShoppingList\BuildShoppingList;
use App\Models\CustomShoppingItem;
use App\Models\ShoppingListCustomItem;
use App\Models\ShoppingListItem;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    use AuthorizesRequests;

    public string $rangeStart = '';
    public string $rangeEnd = '';
    public string $totalPrice = '0.00';
    public string $customSearch = '';
    public ?int $customItemId = null;
    public string $customName = '';
    public ?float $customPrice = null;
    public ?float $customQuantity = null;

    /**
     * @var array<int, array{id: int, name: string, unit: string|null, quantity: string|null, display_quantity: string|null, price: string|null, checked_at: string|null, source_recipes_count: int, is_custom: bool}>
     */
    public array $items = [];

    public function mount(): void
    {
        $start = CarbonImmutable::now()->startOfWeek(CarbonImmutable::MONDAY);
        $end = $start->addDays(6);

        $this->rangeStart = $start->toDateString();
        $this->rangeEnd = $end->toDateString();

        $this->generate();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'rangeStart' => ['required', 'date'],
            'rangeEnd' => ['required', 'date', 'after_or_equal:rangeStart'],
        ];
    }

    public function generate(): void
    {
        $validated = $this->validate();

        $this->items = app(BuildShoppingList::class)->handle(
            Auth::user(),
            CarbonImmutable::parse($validated['rangeStart']),
            CarbonImmutable::parse($validated['rangeEnd']),
        );

        $total = collect($this->items)
            ->sum(function (array $item): float {
                return (float) ($item['price'] ?? 0);
            });

        $this->totalPrice = number_format($total, 2, '.', '');
    }

    #[Computed]
    public function customItems(): array
    {
        $query = CustomShoppingItem::query()
            ->where('user_id', Auth::id());

        if (trim($this->customSearch) !== '') {
            $query->where('name', 'like', '%'.trim($this->customSearch).'%');
        }

        return $query
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'price'])
            ->toArray();
    }

    public function updatedCustomItemId(?int $value): void
    {
        if (! $value) {
            return;
        }

        $item = CustomShoppingItem::query()
            ->where('user_id', Auth::id())
            ->find($value);

        if (! $item) {
            return;
        }

        $this->customName = $item->name;
        $this->customPrice = (float) $item->price;
    }

    public function selectCustomItem(int $itemId): void
    {
        $this->customItemId = $itemId;
        $this->updatedCustomItemId($itemId);
        $this->customSearch = '';
    }

    public function addCustomItem(): void
    {
        $name = trim($this->customName);
        $price = $this->customPrice;
        $quantity = $this->customQuantity;

        if (! $this->customItemId && $name === '') {
            throw ValidationException::withMessages([
                'customName' => __('Enter a custom item name.'),
            ]);
        }

        if ($price !== null && $price < 0) {
            throw ValidationException::withMessages([
                'customPrice' => __('Price must be zero or greater.'),
            ]);
        }

        if ($quantity !== null && $quantity < 1) {
            throw ValidationException::withMessages([
                'customQuantity' => __('Quantity must be at least 1.'),
            ]);
        }

        $user = Auth::user();

        if ($this->customItemId) {
            $customItem = CustomShoppingItem::query()
                ->where('user_id', $user->id)
                ->findOrFail($this->customItemId);

            if ($price === null) {
                $price = (float) $customItem->price;
            } else {
                $customItem->update(['price' => $price]);
            }
        } else {
            $customItem = CustomShoppingItem::query()
                ->where('user_id', $user->id)
                ->whereRaw('lower(name) = ?', [Str::lower($name)])
                ->first();

            if ($customItem) {
                if ($price !== null) {
                    $customItem->update(['price' => $price]);
                }
                $price = $price ?? (float) $customItem->price;
            } else {
                $customItem = CustomShoppingItem::query()->create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'price' => $price ?? 0,
                ]);
                $price = $price ?? 0;
            }
        }

        $quantity = $quantity ?? 1;
        $totalPrice = round((float) $price * (float) $quantity, 2);

        ShoppingListCustomItem::query()->updateOrCreate([
            'user_id' => $user->id,
            'custom_shopping_item_id' => $customItem->id,
            'range_start' => $this->rangeStart,
            'range_end' => $this->rangeEnd,
        ], [
            'quantity' => $quantity,
            'price' => $totalPrice,
            'checked_at' => null,
        ]);

        $this->resetCustomForm();
        $this->generate();
    }

    public function toggleCustomItem(int $itemId): void
    {
        $item = ShoppingListCustomItem::query()->where('user_id', Auth::id())->findOrFail($itemId);
        $checkedAt = $item->checked_at ? null : now();

        $item->update(['checked_at' => $checkedAt]);

        $this->items = collect($this->items)
            ->map(function (array $entry) use ($itemId, $checkedAt): array {
                if (($entry['is_custom'] ?? false) && $entry['id'] === $itemId) {
                    $entry['checked_at'] = $checkedAt?->toDateTimeString();
                }

                return $entry;
            })
            ->all();
    }

    protected function resetCustomForm(): void
    {
        $this->customItemId = null;
        $this->customSearch = '';
        $this->customName = '';
        $this->customPrice = null;
        $this->customQuantity = null;
    }

    public function toggleItem(int $itemId): void
    {
        $item = ShoppingListItem::query()->where('user_id', Auth::id())->findOrFail($itemId);
        $checkedAt = $item->checked_at ? null : now();

        $item->update(['checked_at' => $checkedAt]);

        $this->items = collect($this->items)
            ->map(function (array $entry) use ($itemId, $checkedAt): array {
                if ($entry['id'] === $itemId) {
                    $entry['checked_at'] = $checkedAt?->toDateTimeString();
                }

                return $entry;
            })
            ->all();
    }
};
?>

<section class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Shopping List') }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Generate an aggregated list from planned meals.') }}</p>
        </div>

        <x-ui.button size="sm" variant="secondary" wire:click="generate">
            {{ __('Refresh list') }}
        </x-ui.button>
    </div>

    <x-ui.card class="p-6 space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.input wire:model="rangeStart" name="rangeStart" :label="__('Start date')" type="date" />
            <x-ui.input wire:model="rangeEnd" name="rangeEnd" :label="__('End date')" type="date" />
            <div class="flex items-end">
                <x-ui.button variant="primary" wire:click="generate" data-test="shopping-generate">
                    {{ __('Generate') }}
                </x-ui.button>
            </div>
        </div>

        <x-ui.card class="p-4 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Custom items') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Add items that are not part of recipes.') }}</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="grid gap-2">
                    <x-ui.input wire:model.live="customSearch" name="customSearch" :label="__('Search saved')" placeholder="{{ __('Search saved items') }}" />
                    @if ($customSearch !== '')
                        <div class="rounded-xl border border-slate-200 bg-white p-2 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <div class="grid gap-1">
                                @forelse ($this->customItems as $customItem)
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between rounded-lg px-2 py-1 text-left hover:bg-slate-50 dark:hover:bg-slate-800"
                                        wire:click="selectCustomItem({{ $customItem['id'] }})"
                                    >
                                        <span>{{ $customItem['name'] }}</span>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $customItem['price'] }}</span>
                                    </button>
                                @empty
                                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('No matches.') }}</span>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>

                <div class="grid gap-1">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-200" for="customItemId">{{ __('Saved items') }}</label>
                    <select
                        id="customItemId"
                        name="customItemId"
                        wire:model.live="customItemId"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                    >
                        <option value="">{{ __('Select saved item') }}</option>
                        @foreach ($this->customItems as $customItem)
                            <option value="{{ $customItem['id'] }}">{{ $customItem['name'] }}</option>
                        @endforeach
                    </select>
                    @error('customItemId')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.input wire:model="customName" name="customName" :label="__('Name')" placeholder="{{ __('E.g. Coffee') }}" />

                <div class="grid grid-cols-2 gap-3">
                    <x-ui.input wire:model="customQuantity" name="customQuantity" :label="__('Qty')" type="number" step="0.01" min="1" />
                    <x-ui.input wire:model="customPrice" name="customPrice" :label="__('Price')" type="number" step="0.01" min="0" />
                </div>
            </div>

            <div class="flex items-center justify-end">
                <x-ui.button size="sm" variant="primary" wire:click="addCustomItem">
                    {{ __('Add custom item') }}
                </x-ui.button>
            </div>
        </x-ui.card>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-300">
            <span>{{ __('Total for range') }}</span>
            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Approx.') }} ${{ rtrim(rtrim($totalPrice, '0'), '.') }}</span>
        </div>

        <div class="grid gap-3">
            @forelse ($items as $item)
                <x-ui.card class="p-4">
                    <label class="flex flex-wrap items-center justify-between gap-4">
                        <span class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 dark:border-slate-600 dark:bg-slate-900"
                                @checked($item['checked_at'])
                                wire:click="{{ $item['is_custom'] ? 'toggleCustomItem' : 'toggleItem' }}({{ $item['id'] }})"
                                data-test="shopping-item-{{ $item['id'] }}"
                            />
                            <span class="grid gap-1">
                                <span class="font-medium text-slate-900 dark:text-slate-100">{{ $item['name'] }}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    @if ($item['display_quantity'])
                                        {{ $item['display_quantity'] }} {{ $item['unit'] }}
                                    @else
                                        {{ __('As needed') }}
                                    @endif
                                    @if ($item['source_recipes_count'] > 0)
                                        · {{ $item['source_recipes_count'] }} {{ __('recipes') }}
                                    @endif
                                    @if ($item['is_custom'])
                                        · {{ __('Custom') }}
                                    @endif
                                    @if ($item['price'])
                                        · {{ __('Approx.') }} ${{ $item['price'] }}
                                    @endif
                                </span>
                            </span>
                        </span>

                        <span class="text-xs text-slate-400 dark:text-slate-500">
                            {{ $item['checked_at'] ? __('Checked') : __('Open') }}
                        </span>
                    </label>
                </x-ui.card>
            @empty
                <x-ui.card class="p-10 text-center">
                    <div class="space-y-3">
                        <div class="text-3xl" aria-hidden="true">🥬</div>
                        <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('No items yet. Add meals to your plan and generate a list.') }}</p>
                        <x-ui.button variant="primary" :href="route('meal-plan.index')">
                            {{ __('Plan meals') }}
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endforelse
        </div>
    </x-ui.card>
</section>
