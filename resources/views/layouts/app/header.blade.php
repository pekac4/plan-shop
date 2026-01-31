<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
                <flux:tooltip :content="__('Repository')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="folder-git-2"
                        href="https://github.com/laravel/livewire-starter-kit"
                        target="_blank"
                        :label="__('Repository')"
                    />
                </flux:tooltip>
                <flux:tooltip :content="__('Documentation')" position="bottom">
                    <flux:navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="book-open-text"
                        href="https://laravel.com/docs/starter-kits#livewire"
                        target="_blank"
                        :label="__('Documentation')"
                    />
                </flux:tooltip>
            </flux:navbar>

            <x-locale-switcher class="me-2" />

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item
                        icon="layout-grid"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        class="nav-tone-dashboard"
                    >
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Meal Planner')">
                    <flux:sidebar.item
                        icon="book-open-text"
                        :href="route('recipes.index')"
                        :current="request()->routeIs('recipes.*')"
                        class="nav-tone-meal"
                    >
                        {{ __('Recipes') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="calendar"
                        :href="route('meal-plan.index')"
                        :current="request()->routeIs('meal-plan.*')"
                        class="nav-tone-meal"
                    >
                        {{ __('Meal Plan') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="layout-grid"
                        :href="route('shopping-list.index')"
                        :current="request()->routeIs('shopping-list.*')"
                        class="nav-tone-meal"
                    >
                        {{ __('Shopping List') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Highlights')">
                    <flux:sidebar.item
                        icon="chef-hat"
                        :href="route('highlights.chef-of-month')"
                        :current="request()->routeIs('highlights.chef-of-month')"
                        class="nav-tone-highlight"
                    >
                        {{ __('Chef of the Month') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="crown"
                        :href="route('highlights.king-of-recipe')"
                        :current="request()->routeIs('highlights.king-of-recipe')"
                        class="nav-tone-highlight"
                    >
                        {{ __('King of the Recipe') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item
                        icon="sparkles"
                        :href="route('highlights.upcoming-chef')"
                        :current="request()->routeIs('highlights.upcoming-chef')"
                        class="nav-tone-highlight"
                    >
                        {{ __('Upcoming Chef') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
