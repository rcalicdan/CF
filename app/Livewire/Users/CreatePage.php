<?php

namespace App\Livewire\Users;

use App\ActionService\UserService;
use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreatePage extends Component
{
    use WithFileUploads;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = '';
    public $profile_path;

    protected UserService $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function mount()
    {
        $this->role = UserRoles::EMPLOYEE->value;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
            'role' => 'required|in:' . implode(',', array_column(UserRoles::cases(), 'value')),
            'profile_path' => 'nullable|image|max:2048',
        ];
    }

    public function validationAttributes()
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'password_confirmation' => 'password confirmation',
            'profile_path' => 'profile picture',
        ];
    }

    public function save()
    {
        $this->authorize('create', User::class);
        $this->validate();

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
        ];

        if ($this->profile_path) {
            $data['profile_path'] = $this->profile_path->store('profiles', 'public');
        }

        try {
            $user = $this->userService->storeNewUser($data);

            session()->flash('success', 'Użytkownik został pomyślnie utworzony!');

            return redirect()->route('users.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas tworzenia użytkownika. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        $this->authorize('create', User::class);
        return view('livewire.users.create-page', [
            'roleOptions' => UserRoles::options()
        ]);
    }
}
