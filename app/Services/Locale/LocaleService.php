<?php

namespace App\Services\Locale;

use App\Http\Requests\UpdateLocaleRequest;

class LocaleService
{
    public function update(UpdateLocaleRequest $request, string $locale): void
    {
        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        $user = $request->user();

        if ($user) {
            $user->forceFill([
                'locale' => $locale,
            ])->save();
        }
    }
}
