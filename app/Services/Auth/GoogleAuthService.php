<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleAuthService
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleCallback(): RedirectResponse
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

        return redirect()->intended(route('dashboard'));
    }

    private function storeAvatar(User $user, string $avatarUrl): void
    {
        $avatarUrl = trim($avatarUrl);
        $urlParts = parse_url($avatarUrl);

        if (! is_array($urlParts) || ! isset($urlParts['scheme'], $urlParts['host'])) {
            return;
        }

        $scheme = strtolower((string) $urlParts['scheme']);
        $host = strtolower((string) $urlParts['host']);

        if ($scheme !== 'https' || ! $this->isAllowedAvatarHost($host)) {
            return;
        }

        try {
            $response = Http::timeout(5)
                ->connectTimeout(3)
                ->accept('image/*')
                ->withOptions(['allow_redirects' => false])
                ->get($avatarUrl);
        } catch (ConnectionException) {
            return;
        }

        if (! $response->successful()) {
            return;
        }

        $maxBytes = (int) config('services.google.avatar_max_bytes', 2 * 1024 * 1024);
        $contentLengthHeader = $response->header('Content-Length');
        $contentLength = is_numeric($contentLengthHeader) ? (int) $contentLengthHeader : 0;

        if ($contentLength > 0 && $contentLength > $maxBytes) {
            return;
        }

        $body = $response->body();

        if ($body === '' || strlen($body) > $maxBytes) {
            return;
        }

        $imageInfo = @getimagesizefromstring($body);
        $mime = is_array($imageInfo) ? $imageInfo['mime'] : '';

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/jpeg', 'image/jpg' => 'jpg',
            default => null,
        };

        if (! $extension) {
            return;
        }

        $path = 'avatars/'.Str::uuid().'.'.$extension;

        Storage::disk('public')->put($path, $body);

        $user->forceFill([
            'avatar_path' => $path,
        ])->save();
    }

    private function isAllowedAvatarHost(string $host): bool
    {
        $host = Str::lower($host);

        $allowedHosts = config('services.google.avatar_allowed_hosts', []);
        if (in_array($host, $allowedHosts, true)) {
            return true;
        }

        $allowedSuffix = (string) config('services.google.avatar_allowed_host_suffix', 'googleusercontent.com');

        return $host === $allowedSuffix || Str::endsWith($host, '.'.$allowedSuffix);
    }
}
