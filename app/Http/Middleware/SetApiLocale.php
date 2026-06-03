<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    /**
     * Set the response locale before API resources resolve translated fields.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        config(['app.locale' => $locale]);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $requested = $request->header('X-Localization') ?: $request->query('lang', 'ar');

        $requested = Str::lower(Str::before($requested, ','));

        if (Str::startsWith($requested, 'en')) {
            return 'en';
        }

        return 'ar';
    }
}
