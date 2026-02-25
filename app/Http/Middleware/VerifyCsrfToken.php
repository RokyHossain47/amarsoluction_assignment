<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     */

    public function handle($request, \Closure $next)
    {
       logger('API key header: ' . $request->header('apikey'));

        return $next($request);
    }
}