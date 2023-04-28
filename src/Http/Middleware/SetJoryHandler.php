<?php

namespace JosKolenberg\LaravelJory\Http\Middleware;

use Closure;

/**
 * @deprecated
 *
 * Class is kept to prevent breaking changes.
 * But it is no longer needed since the error response handling is now done in the exceptions itself.
 *
 * Any references to this class in your code can be removed.
 */
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
        return $next($request);
    }
}
