<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = request(
            'lang',
            data_get(Language::where('default', 1)->first(['locale']), 'locale')
        );

        app()->setLocale($locale);
        return $next($request);
    }
}
