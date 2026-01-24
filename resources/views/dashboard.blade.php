<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-slate-900">{{ __('Dashboard') }}</h1>
            <p class="text-sm text-slate-600">{{ __('Quick access to your meal planning tools.') }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-5">
                <div class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Recipes') }}</h2>
                    <p class="text-sm text-slate-600">{{ __('Manage your saved recipes and ingredients.') }}</p>
                    <x-ui.button size="sm" variant="secondary" :href="route('recipes.index')" wire:navigate>
                        {{ __('View recipes') }}
                    </x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Meal Plan') }}</h2>
                    <p class="text-sm text-slate-600">{{ __('Plan meals across your week.') }}</p>
                    <x-ui.button size="sm" variant="secondary" :href="route('meal-plan.index')" wire:navigate>
                        {{ __('Open planner') }}
                    </x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="space-y-3">
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Shopping List') }}</h2>
                    <p class="text-sm text-slate-600">{{ __('Generate a list from planned meals.') }}</p>
                    <x-ui.button size="sm" variant="secondary" :href="route('shopping-list.index')" wire:navigate>
                        {{ __('View list') }}
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts::app>
