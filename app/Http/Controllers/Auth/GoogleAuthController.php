<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (request()->has('error')) {
            return redirect()
                ->route('login')
                ->with('status', __('Google sign-in was cancelled.'));
        }

        $socialUser = Socialite::driver('google')->user();
        $email = $socialUser->getEmail();

        if (! $email) {
            return redirect()
                ->route('login')
                ->with('status', __('Google did not provide an email address.'));
        }

        $user = User::query()->where('google_id', $socialUser->getId())->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();
        }

        if (! $user) {
            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Google User',
                'email' => $email,
                'google_id' => $socialUser->getId(),
                'password' => Hash::make(Str::password(32)),
                'email_verified_at' => now(),
                'locale' => app()->getLocale(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $user->google_id ?: $socialUser->getId(),
                'name' => $user->name ?: ($socialUser->getName() ?: $socialUser->getNickname() ?: $user->name),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
        }

        if (! $user->avatar_path && $socialUser->getAvatar()) {
            $this->storeAvatar($user, $socialUser->getAvatar());
        }

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    private function storeAvatar(User $user, string $avatarUrl): void
    {
        $response = Http::timeout(5)->get($avatarUrl);

        if (! $response->successful()) {
            return;
        }

        $contentType = $response->header('Content-Type', 'image/jpeg');
        $extension = match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            default => 'jpg',
        };

        $path = 'avatars/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($path, $response->body());

        $user->forceFill([
            'avatar_path' => $path,
        ])->save();
    }
}
