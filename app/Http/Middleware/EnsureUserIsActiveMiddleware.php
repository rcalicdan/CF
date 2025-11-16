<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            if (!$user->isActive()) {
                Auth::logout();
                
                if ($request->isJson()) {
                    return response()->json([
                        'message' => 'Your account has been deactivated.'
                    ], 403);
                }
                
                return redirect()->route('login')
                    ->with('error', 'Twoje konto zostało dezaktywowane. Skontaktuj się z administratorem.');
            }
        }
        
        return $next($request);
    }
}