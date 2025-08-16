<?php

namespace App\Traits;

use App\ActionService\Api\AuthApiService;
use Illuminate\Support\Facades\Auth;

trait ManagesApiTokens
{
    public function ensureValidToken(): bool
    {
        if (! session('api_token')) {
            return false;
        }

        // You could add token validation logic here
        return true;
    }

    public function handleTokenExpiry(): void
    {
        session()->forget('api_token');
        Auth::logout();

        session()->flash('warning', 'Your session has expired. Please log in again.');
        redirect()->route('login');
    }

    public function refreshTokenIfNeeded(): bool
    {
        if (! $this->ensureValidToken()) {
            return false;
        }

        try {
            $apiService = app(AuthApiService::class);
            $response = $apiService->refreshToken();

            if ($response->successful()) {
                $data = $response->json();
                session(['api_token' => $data['token']]);

                return true;
            }
        } catch (\Exception $e) {
            $this->handleTokenExpiry();
        }

        return false;
    }
}
