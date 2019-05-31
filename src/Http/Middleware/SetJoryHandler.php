<?php

namespace JosKolenberg\LaravelJory\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
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
        app()->singleton(
            ExceptionHandler::class,
            JoryHandler::class
        );

        return $next($request);
    }
}
