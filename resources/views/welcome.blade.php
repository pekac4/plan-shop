<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Plan&Shop') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 text-slate-700 antialiased">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col gap-12 px-4 py-10">
            <header class="flex items-center justify-between">
                <x-app-logo href="{{ route('home') }}" />
                <div class="flex items-center gap-3 text-sm">
                    <a class="font-medium text-slate-700 hover:text-slate-900" href="{{ route('login') }}">{{ __('Log in') }}</a>
                    <x-ui.button variant="primary" :href="route('register')">{{ __('Get started free') }}</x-ui.button>
                    <x-locale-switcher />
                </div>
            </header>

            <section class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="space-y-6">
                    <div class="space-y-3">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-600">{{ __('Plan&Shop') }}</p>
                        <h1 class="text-4xl font-semibold text-slate-900 sm:text-5xl">
                            {{ __('Plan meals. Shop smarter. Cook better.') }}
                        </h1>
                        <p class="text-lg text-slate-600">
                            {{ __('Your personal meal planner, recipe book, and shopping list — all in one place.') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.button variant="primary" :href="route('register')">{{ __('Get started free') }}</x-ui.button>
                        <x-ui.button variant="secondary" :href="route('recipes.index')">{{ __('Browse recipes') }}</x-ui.button>
                    </div>
                    <x-ui.share-links
                        :url="route('home')"
                        :text="__('Plan&Shop makes meal planning simple. Join me!')"
                        :label="__('Share the app')"
                    />
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex h-full flex-col gap-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                            {{ __('Recipe of the Month') }}
                        </div>
                        @php
                            $recipeImageId = $recipeOfMonth?->id ? 'home-recipe-image-'.$recipeOfMonth->id : 'home-recipe-image';
                        @endphp
                        <x-ui.recipe-image
                            :id="$recipeImageId"
                            :title="$recipeOfMonth?->title"
                            :image="$recipeOfMonth?->cover_image_url"
                            :thumbnail="$recipeOfMonth?->cover_thumbnail_url"
                            emoji="🥬"
                            container-class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50"
                            image-class="h-48 w-full object-cover"
                            placeholder-class="text-3xl"
                        />
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-emerald-700">{{ $monthLabel }}</p>
                            <h2 class="text-2xl font-semibold text-slate-900">
                                {{ $recipeOfMonth?->title ?? __('Roasted Veggie Bowl with Lemon Tahini') }}
                            </h2>
                            <p class="text-sm text-slate-500">
                                @php
                                    $handle = $recipeOfMonth?->user?->name
                                        ? '@'.\Illuminate\Support\Str::of($recipeOfMonth->user->name)->lower()->slug('_')
                                        : '@maria_k';
                                @endphp
                                {{ __('by') }} {{ $handle }}
                            </p>
                            <p class="text-sm text-slate-600">
                                {{ $recipeOfMonth?->description ?? __('Fresh, fast, and perfect for busy weeks — ready in 25 minutes.') }}
                            </p>
                        </div>
                        @if ($recipeOfMonth)
                            <x-ui.button variant="primary" :href="route('recipes.edit', $recipeOfMonth)">
                                {{ __('View recipe') }}
                            </x-ui.button>
                        @else
                            <x-ui.button variant="primary" :href="route('recipes.index')">
                                {{ __('View recipe') }}
                            </x-ui.button>
                        @endif
                        <x-ui.share-links
                            :url="$recipeOfMonth ? route('recipes.edit', $recipeOfMonth) : route('recipes.index')"
                            :text="$recipeOfMonth?->title ? __('Try this recipe: :title', ['title' => $recipeOfMonth->title]) : __('Try this recipe on Plan&Shop')"
                            :label="__('Share this recipe')"
                        />
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="space-y-6">
                    <div class="space-y-2">
                        <h2 class="text-2xl font-semibold text-slate-900">
                            {{ __('Stop wasting time deciding what to cook every day.') }}
                        </h2>
                        <p class="text-slate-600">
                            {{ __('With Plan&Shop you can:') }}
                        </p>
                    </div>
                    <ul class="grid gap-3 text-slate-600">
                        <li class="flex items-start gap-3">
                            <span class="mt-1 text-emerald-600">✓</span>
                            {{ __('Save and organize your favorite recipes in your own recipe book') }}
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 text-emerald-600">✓</span>
                            {{ __('Plan your week in minutes with a simple meal planner') }}
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 text-emerald-600">✓</span>
                            {{ __('Generate a shopping list automatically from your planned meals') }}
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 text-emerald-600">✓</span>
                            {{ __('Copy and remix recipes from millions of free public community recipes') }}
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-1 text-emerald-600">✓</span>
                            {{ __('Rate other recipes, leave comments, and interact with the community') }}
                        </li>
                    </ul>
                    <p class="text-slate-600">
                        {{ __('Create your account, add your first recipe, and your next week is already planned.') }}
                    </p>
                    <x-ui.button variant="primary" :href="route('register')">
                        {{ __('Create your free account') }}
                    </x-ui.button>
                </div>
            </section>
        </div>
    </body>
</html>
