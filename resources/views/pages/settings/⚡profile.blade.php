<?php

use App\Concerns\ProfileValidationRules;
use App\ImageResizer;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use ProfileValidationRules;
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $locale = 'en';
    public $avatar = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->locale = Auth::user()->locale ?? config('app.locale');
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate(array_merge(
            $this->profileRules($user->id),
            [
                'locale' => [
                    'required',
                    'string',
                    Rule::in(config('app.supported_locales', ['en', 'sr'])),
                ],
            ],
            ['avatar' => ['nullable', File::image()->max(2 * 1024)]],
        ));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->storeAvatar($user);

        app()->setLocale($this->locale);
        if (request()->hasSession()) {
            request()->session()->put('locale', $this->locale);
        }

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

    protected function storeAvatar(User $user): void
    {
        if (! $this->avatar) {
            return;
        }

        $disk = Storage::disk('public');
        $directory = 'avatars/'.$user->id;
        $extension = strtolower((string) $this->avatar->getClientOriginalExtension()) ?: 'jpg';
        $avatarPath = $directory.'/avatar.'.$extension;

        $disk->makeDirectory($directory);

        $sourcePath = $this->avatar->getRealPath();
        $avatarFullPath = $disk->path($avatarPath);

        $saved = $sourcePath
            ? ImageResizer::resizeToFit($sourcePath, $avatarFullPath, 256, 256)
            : false;

        if (! $saved) {
            $disk->putFileAs($directory, $this->avatar, 'avatar.'.$extension);
        }

        if ($user->avatar_path && $user->avatar_path !== $avatarPath) {
            $disk->delete($user->avatar_path);
        }

        $user->forceFill(['avatar_path' => $avatarPath])->save();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="space-y-6">
            <div class="flex flex-wrap items-center gap-4">
                @if ($avatar)
                    <img
                        src="{{ $avatar->temporaryUrl() }}"
                        alt="{{ __('Avatar preview') }}"
                        class="h-16 w-16 rounded-full object-cover"
                    />
                @elseif (auth()->user()->avatar_url)
                    <img
                        src="{{ auth()->user()->avatar_url }}"
                        alt="{{ auth()->user()->name }}"
                        class="h-16 w-16 rounded-full object-cover"
                    />
                @else
                    <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" size="lg" />
                @endif

                <div class="flex-1">
                    <x-ui.input wire:model="avatar" name="avatar" :label="__('Avatar image')" type="file" accept="image/*" />
                    <p class="mt-1 text-xs text-slate-500">{{ __('Optional. Square images work best.') }}</p>
                </div>
            </div>

            <x-ui.input wire:model="name" name="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div class="space-y-4">
                <x-ui.input wire:model="email" name="email" :label="__('Email')" type="email" required autocomplete="email" />

                <flux:field>
                    <flux:label>{{ __('Default language') }}</flux:label>
                    <flux:select wire:model="locale" name="locale">
                        <flux:select.option value="en">{{ __('English') }}</flux:select.option>
                        <flux:select.option value="sr">{{ __('Serbian') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="locale" />
                </flux:field>

                @if ($this->hasUnverifiedEmail)
                    <div class="text-sm text-slate-600">
                        <p>{{ __('Your email address is unverified.') }}</p>
                        <button type="button" class="mt-2 text-sm font-medium text-green-700 hover:text-green-800" wire:click.prevent="resendVerificationNotification">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-700">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <x-ui.button variant="primary" type="submit" data-test="update-profile-button">
                    {{ __('Save') }}
                </x-ui.button>

                <x-action-message on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <div class="mt-8">
                <livewire:pages::settings.delete-user-form />
            </div>
        @endif
    </x-pages::settings.layout>
</section>
