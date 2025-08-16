<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to API requests
        if ($request->is('api/*')) {
            $acceptHeader = $request->header('Accept');

            if ($acceptHeader !== 'application/json') {
                $request->headers->set('Accept', 'application/json');
            }
        }

        return $next($request);
    }
}
