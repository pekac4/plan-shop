<section class="relative overflow-hidden py-10">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(70%_60%_at_50%_0%,rgba(16,185,129,0.08),transparent_70%),linear-gradient(180deg,rgba(248,250,252,0.85),rgba(241,245,249,0.95))] dark:bg-[radial-gradient(70%_60%_at_50%_0%,rgba(16,185,129,0.12),transparent_70%),linear-gradient(180deg,rgba(15,23,42,0.95),rgba(2,6,23,0.98))]"></div>

    <div class="mx-auto max-w-5xl">
        <div class="rounded-3xl bg-white/90 shadow-[0_30px_80px_-60px_rgba(15,23,42,0.7)] ring-1 ring-slate-200/70 backdrop-blur dark:bg-slate-900/90 dark:ring-slate-700/60">
            <div class="rounded-3xl bg-[linear-gradient(to_bottom,transparent_0%,transparent_94%,rgba(148,163,184,0.25)_95%,transparent_96%)] bg-[length:100%_32px] bg-[position:0_12px] p-6 sm:p-10 dark:bg-[linear-gradient(to_bottom,transparent_0%,transparent_94%,rgba(71,85,105,0.45)_95%,transparent_96%)]">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-3">
                        <flux:heading size="xl" class="text-slate-900 dark:text-white">
                            {{ $recipe->title }}
                        </flux:heading>
                        <flux:text class="text-slate-600 dark:text-slate-300">
                            {{ $recipe->description ?: __('A cozy recipe note awaits.') }}
                        </flux:text>
                    </div>

                    <div class="flex shrink-0 justify-start lg:justify-end">
                        @if ($coverImageUrl || $coverThumbnailUrl)
                            <flux:modal.trigger name="recipe-cover">
                                <button
                                    type="button"
                                    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 dark:border-slate-700 dark:bg-slate-900"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', 'recipe-cover')"
                                    aria-label="{{ __('Open cover image') }}"
                                >
                                    <img
                                        src="{{ $coverThumbnailUrl ?? $coverImageUrl }}"
                                        alt="{{ $recipe->title }}"
                                        class="h-40 w-40 object-cover sm:h-44 sm:w-44 md:h-48 md:w-48"
                                        loading="lazy"
                                    />
                                    <span class="pointer-events-none absolute inset-0 rounded-2xl ring-1 ring-black/5 transition group-hover:ring-emerald-300/70 dark:ring-white/10 dark:group-hover:ring-emerald-200/60"></span>
                                </button>
                            </flux:modal.trigger>
                        @else
                            <div class="flex h-40 w-40 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white/70 text-3xl text-slate-500 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-400 sm:h-44 sm:w-44 md:h-48 md:w-48">
                                🥬
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <x-ui.button variant="secondary" :href="route('recipes.index')">
                        {{ __('Back to recipes') }}
                    </x-ui.button>
                    @if ($canEdit)
                        <x-ui.button variant="edit" :href="route('recipes.edit', $recipe)">
                            {{ __('Edit') }}
                        </x-ui.button>
                    @endif
                </div>

                <div class="mt-6">
                    <x-ui.share-links
                        :url="route('recipes.show', $recipe)"
                        :text="__('Try this recipe: :title', ['title' => $recipe->title])"
                        :label="__('Share this recipe')"
                    />
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Servings') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ $recipe->servings ?? '—' }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Prep time') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ $recipe->prep_time_minutes !== null ? $recipe->prep_time_minutes.' '.__('min') : '—' }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Cook time') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ $recipe->cook_time_minutes !== null ? $recipe->cook_time_minutes.' '.__('min') : '—' }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Approx cost') }}</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ $totalCost !== null ? '$'.number_format($totalCost, 2) : '—' }}
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Source URL') }}</p>
                        <p class="mt-2 text-sm font-medium">
                            @if ($recipe->source_url)
                                <a class="text-emerald-700 underline-offset-4 hover:underline dark:text-emerald-300" href="{{ $recipe->source_url }}" target="_blank" rel="noopener noreferrer">
                                    {{ $recipe->source_url }}
                                </a>
                            @else
                                <span class="text-slate-500 dark:text-slate-400">—</span>
                            @endif
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/80">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Visibility') }}</p>
                        <div class="mt-2">
                            @if ($recipe->is_public)
                                <x-ui.badge tone="emerald">{{ __('Public') }}</x-ui.badge>
                            @else
                                <x-ui.badge tone="amber">{{ __('Private') }}</x-ui.badge>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-10">
                    <flux:heading size="lg" class="text-slate-900 dark:text-white">{{ __('Instructions') }}</flux:heading>
                    <div class="mt-3 text-slate-700 dark:text-slate-200">
                        @if ($instructionLines === [])
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('No instructions yet.') }}</p>
                        @elseif ($useInstructionList)
                            <ol class="list-decimal space-y-2 pl-6">
                                @foreach ($instructionLines as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ol>
                        @else
                            <p>{{ $instructionLines[0] }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-10">
                    <div class="flex items-center justify-between gap-4">
                        <flux:heading size="lg" class="text-slate-900 dark:text-white">{{ __('Ingredients') }}</flux:heading>
                        <span class="text-sm text-slate-500 dark:text-slate-400">
                            {{ count($ingredients) }} {{ __('items') }}
                        </span>
                    </div>

                    <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200/70 bg-white/90 shadow-sm dark:border-slate-700/70 dark:bg-slate-900/90">
                        <table class="min-w-full divide-y divide-slate-200/60 text-sm dark:divide-slate-700/60">
                            <thead class="bg-slate-50/80 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Qty') }}</th>
                                    <th class="px-4 py-3">{{ __('Unit') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Price') }}</th>
                                    <th class="px-4 py-3">{{ __('Note') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/60 dark:divide-slate-700/60">
                                @forelse ($ingredients as $ingredient)
                                    <tr class="text-slate-700 dark:text-slate-200">
                                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                            {{ $ingredient['name'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums">
                                            {{ $ingredient['quantity'] !== null && $ingredient['quantity'] !== '' ? number_format((float) $ingredient['quantity'], 2) : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $ingredient['unit'] ?: '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums">
                                            {{ $ingredient['price'] !== null && $ingredient['price'] !== '' ? '$'.number_format((float) $ingredient['price'], 2) : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                                            {{ $ingredient['note'] ?: '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                                            {{ __('No ingredients added yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($totalCost !== null)
                        <div class="mt-4 flex justify-end text-sm text-slate-600 dark:text-slate-300">
                            <div class="rounded-full bg-emerald-50 px-4 py-2 font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                {{ __('Total') }}: ${{ number_format($totalCost, 2) }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($coverImageUrl || $coverThumbnailUrl)
        <flux:modal name="recipe-cover" focusable class="max-w-4xl">
            <div class="relative">
                <img
                    src="{{ $coverImageUrl ?? $coverThumbnailUrl }}"
                    alt="{{ $recipe->title }}"
                    class="max-h-[80vh] w-full rounded-2xl object-contain"
                />
                <div class="absolute right-4 top-4">
                    <flux:modal.close>
                        <flux:button variant="filled" size="sm">{{ __('Close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    @endif
</section>
