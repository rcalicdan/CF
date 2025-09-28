<?php

namespace App\Livewire\Auth;

use App\ActionService\AuthService;
use App\Traits\DispatchFlashMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    use DispatchFlashMessage;

    public $first_name = '';

    public $last_name = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public $role = 'employee';

    public $showPassword = false;

    public $terms_accepted = false;

    protected $authService;

    public function boot(AuthService $authService)
    {
        $this->authService = $authService;
    }

    protected function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,driver,employee',
            'terms_accepted' => 'required|accepted',
        ];
    }

    protected function messages()
    {
        return [
            'first_name.required' => __('validation.first_name_required'),
            'first_name.max' => __('validation.first_name_max'),
            'last_name.required' => __('validation.last_name_required'),
            'last_name.max' => __('validation.last_name_max'),
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_invalid'),
            'email.unique' => __('validation.email_unique'),
            'password.required' => __('validation.password_required'),
            'password.min' => __('validation.password_min'),
            'password.confirmed' => __('validation.password_confirmed'),
            'role.required' => __('validation.role_required'),
            'role.in' => __('validation.role_invalid'),
            'terms_accepted.accepted' => __('validation.terms_required'),
        ];
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = ! $this->showPassword;
    }

    public function register()
    {
        $this->validate();

        try {
            $userData = [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'role' => $this->role,
                'email_verified_at' => now(),
            ];

            $user = $this->authService->registerUser($userData);
            $token = $this->authService->generateToken($user);
            session(['api_token' => $token]);
            Auth::login($user);

            $this->dispatchFlashMessage('success', 'Rejestracja zakończona pomyślnie.');
            $this->dispatch('user-registered', $user->toArray());

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            $this->dispatchFlashMessage('error', 'Wystąpił błąd podczas rejestracji.');
        }
    }

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
