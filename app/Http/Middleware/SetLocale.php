<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = config('app.supported_locales', ['en', 'sr']);
        $locale = null;

        if ($request->user() && in_array($request->user()->locale, $supportedLocales, true)) {
            $locale = $request->user()->locale;
        }

        if (! $locale && $request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');
            if (in_array($sessionLocale, $supportedLocales, true)) {
                $locale = $sessionLocale;
            }
        }

        $locale ??= config('app.locale');

        App::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
