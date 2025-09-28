<?php

namespace App\Livewire\Auth;

use App\ActionService\AuthService;
use App\Traits\DispatchFlashMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    use DispatchFlashMessage;

    public $email = '';

    public $password = '';

    public $remember = false;

    public $showPassword = false;

    protected $authService;

    public function boot(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->intended('/');
        }
    }

    protected function rules()
    {
        return [
            'email' => 'required|email|max:60',
            'password' => 'required|string|max:60',
        ];
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = ! $this->showPassword;
    }

    public function login()
    {
        $this->validate();

        try {
            $credentials = [
                'email' => $this->email,
                'password' => $this->password,
            ];

            $user = $this->authService->authenticateUser($credentials);

            if ($user) {
                $token = $this->authService->generateToken($user);
                session(['api_token' => $token]);

                if ($this->remember) {
                    Auth::login($user, true);
                }

                $this->dispatchFlashMessage('success', 'Zalogowano pomyślnie! Przekierowywanie...');

                $this->dispatch('user-logged-in', $user->toArray());

                return $this->redirectIntended('/', false);
            } else {
                $this->dispatchFlashMessage('error', 'Nieprawidłowe dane logowania');
            }
        } catch (\Exception $e) {
            $this->dispatchFlashMessage('error', 'Wystąpił błąd podczas logowania. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
