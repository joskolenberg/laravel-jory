<?php

namespace JosKolenberg\LaravelJory\Http\Middleware;

use Closure;
use JosKolenberg\LaravelJory\Exceptions\JoryHandler;

class SetJoryHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        \App::singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            JoryHandler::class
        );

        return $next($request);
    }
}
