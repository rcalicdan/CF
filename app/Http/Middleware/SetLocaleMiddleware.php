<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocale($request);

        if ($this->isValidLocale($locale)) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    private function getLocale(Request $request): string
    {
        return $request->get('lang')
            ?? $request->header('Accept-Language', config('app.locale'))
            ?? config('app.locale');
    }

    private function isValidLocale(string $locale): bool
    {
        $supportedLocales = ['en', 'pl'];

        if (in_array($locale, $supportedLocales)) {
            return true;
        }

        $shortLocale = substr($locale, 0, 2);
        return in_array($shortLocale, $supportedLocales);
    }
}
