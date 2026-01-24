<?php

use App\Actions\ShoppingList\BuildShoppingList;
use App\Models\ShoppingListItem;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    use AuthorizesRequests;

    public string $rangeStart = '';
    public string $rangeEnd = '';

    /**
     * @var array<int, array{id: int, name: string, unit: string|null, quantity: string|null, display_quantity: string|null, price: string|null, checked_at: string|null, source_recipes_count: int}>
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
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Shopping List') }}</h1>
            <p class="text-sm text-slate-600">{{ __('Generate an aggregated list from planned meals.') }}</p>
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

        <div class="grid gap-3">
            @forelse ($items as $item)
                <x-ui.card class="p-4">
                    <label class="flex flex-wrap items-center justify-between gap-4">
                        <span class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-green-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600"
                                @checked($item['checked_at'])
                                wire:click="toggleItem({{ $item['id'] }})"
                                data-test="shopping-item-{{ $item['id'] }}"
                            />
                            <span class="grid gap-1">
                                <span class="font-medium text-slate-900">{{ $item['name'] }}</span>
                                <span class="text-xs text-slate-500">
                                    @if ($item['display_quantity'])
                                        {{ $item['display_quantity'] }} {{ $item['unit'] }}
                                    @else
                                        {{ __('As needed') }}
                                    @endif
                                    @if ($item['source_recipes_count'] > 0)
                                        · {{ $item['source_recipes_count'] }} {{ __('recipes') }}
                                    @endif
                                    @if ($item['price'])
                                        · {{ __('Approx.') }} ${{ $item['price'] }}
                                    @endif
                                </span>
                            </span>
                        </span>

                        <span class="text-xs text-slate-400">
                            {{ $item['checked_at'] ? __('Checked') : __('Open') }}
                        </span>
                    </label>
                </x-ui.card>
            @empty
                <x-ui.card class="p-10 text-center">
                    <div class="space-y-3">
                        <div class="text-3xl" aria-hidden="true">🥬</div>
                        <p class="text-sm text-slate-600">{{ __('No items yet. Add meals to your plan and generate a list.') }}</p>
                        <x-ui.button variant="primary" :href="route('meal-plan.index')" wire:navigate>
                            {{ __('Plan meals') }}
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endforelse
        </div>
    </x-ui.card>
</section>
