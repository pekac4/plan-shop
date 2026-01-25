<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email Address')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
                class:input="border-emerald-200 border-b-emerald-300/70 focus:border-emerald-300 focus-visible:outline-emerald-300"
            />

            <flux:button
                variant="primary"
                type="submit"
                class="w-full !bg-emerald-100 !text-emerald-700 !border !border-emerald-200 hover:!bg-emerald-200"
                data-test="email-password-reset-link-button"
            >
                {{ __('Email password reset link') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')">{{ __('log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
