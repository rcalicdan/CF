<?php

namespace App\Livewire\Auth;

use App\ActionService\AuthService;
use App\Traits\DispatchFlashMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    use DispatchFlashMessage;

    protected $authService;

    public function boot(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function logout()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $this->performLocalLogout();
            $this->dispatchFlashMessage('success', 'Wylogowano pomyślnie.');

            return redirect()->route('login');
        } catch (\Exception $e) {
            $this->performLocalLogout();
            $this->dispatchFlashMessage('warning', 'Wystąpił błąd podczas wylogowywania.');

            return redirect()->route('login');
        }
    }

    private function performLocalLogout()
    {
        // Revoke all tokens for the user
        if (Auth::user() && Auth::user()->tokens()) {
            Auth::user()->tokens()->delete();
        }

        session()->forget('api_token');
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->dispatch('user-logged-out');
    }

    public function quickLogout()
    {
        $this->logout();
    }

    public function mount()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.auth.logout');
    }
}
