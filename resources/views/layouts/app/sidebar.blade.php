<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        class="nav-tone-dashboard"
                    >
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Meal Planner')" class="grid">
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

                <flux:sidebar.group :heading="__('Highlights')" class="grid">
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

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :avatar="auth()->user()->avatar_url"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :src="auth()->user()->avatar_url"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog">
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
