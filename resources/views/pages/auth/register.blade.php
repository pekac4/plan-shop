<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
                class:input="border-emerald-200 border-b-emerald-300/70 focus:border-emerald-300 focus-visible:outline-emerald-300"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
                class:input="border-emerald-200 border-b-emerald-300/70 focus:border-emerald-300 focus-visible:outline-emerald-300"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
                class:input="border-emerald-200 border-b-emerald-300/70 focus:border-emerald-300 focus-visible:outline-emerald-300"
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
                class:input="border-emerald-200 border-b-emerald-300/70 focus:border-emerald-300 focus-visible:outline-emerald-300"
            />

            <div class="flex items-center justify-end">
                <x-ui.button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </x-ui.button>
            </div>
        </form>

        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white px-2 text-slate-500">{{ __('Or continue with') }}</span>
            </div>
        </div>

        <x-ui.button
            variant="secondary"
            :href="route('auth.google.redirect')"
            class="w-full justify-center gap-2"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#EA4335" d="M12 10.2v3.8h5.3c-.2 1.2-1.4 3.5-5.3 3.5-3.2 0-5.8-2.7-5.8-6s2.6-6 5.8-6c1.8 0 3 .8 3.7 1.5l2.5-2.4C16.8 3 14.7 2 12 2 6.9 2 2.7 6.1 2.7 11.5S6.9 21 12 21c6 0 7.5-4.2 7.5-6.3 0-.4 0-.8-.1-1.1H12z"/>
                <path fill="#34A853" d="M3.9 7.1l3.1 2.3c.8-1.4 2.3-2.4 4.1-2.4 1.8 0 3 .8 3.7 1.5l2.5-2.4C16.8 3 14.7 2 12 2 8.1 2 4.7 4.2 3.9 7.1z"/>
                <path fill="#FBBC05" d="M12 21c2.7 0 5-0.9 6.6-2.4l-3.1-2.4c-.9.6-2 .9-3.5.9-2.7 0-4.9-1.8-5.7-4.2l-3.2 2.5C4.6 18.3 8 21 12 21z"/>
                <path fill="#4285F4" d="M19.4 13.6c.1-.4.1-.7.1-1.1 0-.4 0-.8-.1-1.1H12v2.2h4.3c-.2 1.2-1.4 3.5-5.3 3.5-3.2 0-5.8-2.7-5.8-6S7.8 5.1 11 5.1c1.8 0 3 .8 3.7 1.5l2.5-2.4C15.9 3 13.8 2 11 2 5.9 2 1.7 6.1 1.7 11.5S5.9 21 11 21c6 0 7.5-4.2 7.5-6.3 0-.4 0-.8-.1-1.1z"/>
            </svg>
            {{ __('Continue with Google') }}
        </x-ui.button>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')">{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
