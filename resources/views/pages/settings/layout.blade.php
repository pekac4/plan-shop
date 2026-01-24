<div class="flex flex-col gap-6 md:flex-row">
    <div class="w-full md:w-56">
        <x-ui.card class="p-4 space-y-2">
            <a
                href="{{ route('profile.edit') }}"
                class="block rounded-xl px-3 py-2 text-sm font-medium transition {{ request()->routeIs('profile.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:text-slate-900' }}"
                wire:navigate
                data-test="settings-profile"
            >
                {{ __('Profile') }}
            </a>
            <a
                href="{{ route('user-password.edit') }}"
                class="block rounded-xl px-3 py-2 text-sm font-medium transition {{ request()->routeIs('user-password.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:text-slate-900' }}"
                wire:navigate
                data-test="settings-password"
            >
                {{ __('Password') }}
            </a>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <a
                    href="{{ route('two-factor.show') }}"
                    class="block rounded-xl px-3 py-2 text-sm font-medium transition {{ request()->routeIs('two-factor.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:text-slate-900' }}"
                    wire:navigate
                    data-test="settings-two-factor"
                >
                    {{ __('Two-Factor Auth') }}
                </a>
            @endif
            <a
                href="{{ route('appearance.edit') }}"
                class="block rounded-xl px-3 py-2 text-sm font-medium transition {{ request()->routeIs('appearance.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:text-slate-900' }}"
                wire:navigate
                data-test="settings-appearance"
            >
                {{ __('Appearance') }}
            </a>
        </x-ui.card>
    </div>

    <div class="flex-1">
        <div class="space-y-1">
            <h2 class="text-lg font-semibold text-slate-900">{{ $heading ?? '' }}</h2>
            <p class="text-sm text-slate-600">{{ $subheading ?? '' }}</p>
        </div>

        <div class="mt-4">
            <x-ui.card class="p-6">
                {{ $slot }}
            </x-ui.card>
        </div>
    </div>
</div>
