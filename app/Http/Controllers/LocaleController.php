<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLocaleRequest;
use App\Services\Locale\LocaleService;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __construct(private LocaleService $localeService) {}

    public function __invoke(UpdateLocaleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->localeService->update($request, $data['locale']);

        return redirect()->back();
    }
}
