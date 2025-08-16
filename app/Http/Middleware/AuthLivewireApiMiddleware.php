<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthLivewireApiMiddleware
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guards = $this->normalizeGuards($guards);

        $authenticatedGuard = $this->findAuthenticatedGuard($guards);

        if ($authenticatedGuard) {
            return $this->handleAuthenticatedUser($request, $next, $authenticatedGuard);
        }

        return $this->handleUnauthenticatedUser($request);
    }

    private function normalizeGuards(array $guards): array
    {
        return empty($guards) ? [null] : $guards;
    }

    private function findAuthenticatedGuard(array $guards): ?string
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    private function handleAuthenticatedUser(Request $request, Closure $next, ?string $guard): Response
    {
        Auth::shouldUse($guard);

        if (! session('api_token')) {
            return redirect()->guest(route('login'));
        }

        $request->merge(['authenticated_user' => Auth::user()]);

        return $next($request);
    }

    private function handleUnauthenticatedUser(Request $request): Response
    {
        if ($this->expectsJsonOrLivewireResponse($request)) {
            return $this->createJsonErrorResponse();
        }

        $this->storeIntendedUrl($request);

        return redirect()->guest(route('login'));
    }

    private function expectsJsonOrLivewireResponse(Request $request): bool
    {
        return $request->expectsJson() || $request->is('livewire/*');
    }

    private function createJsonErrorResponse(): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated.',
            'code' => 401,
        ], 401);
    }

    private function storeIntendedUrl(Request $request): void
    {
        if (! $request->is('login') && ! $request->is('register')) {
            session(['url.intended' => $request->url()]);
        }
    }
}
