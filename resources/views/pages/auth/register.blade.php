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
                <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full !bg-emerald-100 !text-emerald-700 !border !border-emerald-200 hover:!bg-emerald-200"
                    data-test="register-user-button"
                >
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')">{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
