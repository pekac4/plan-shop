<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLocaleRequest;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateLocaleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $locale = $data['locale'];

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        if ($request->user()) {
            $request->user()->forceFill([
                'locale' => $locale,
            ])->save();
        }

        return redirect()->back();
    }
}
