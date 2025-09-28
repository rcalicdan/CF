<?php

namespace App\Livewire\Users;

use App\ActionService\UserService;
use App\Enums\UserRoles;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdatePage extends Component
{
    use WithFileUploads;
    public User $user;
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = '';
    public $profile_path;
    public $existing_profile_path;

    protected UserService $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Mount the component and populate the form with the user's existing data.
     */
    public function mount(User $user)
    {
        $this->user = $user;

        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->existing_profile_path = $user->profile_path;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'password' => 'nullable|min:8|confirmed',
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

    /**
     * Update the user's information.
     */
    public function update()
    {
        $this->authorize('update', $this->user);
        $this->validate();

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->profile_path) {
            $data['profile_path'] = $this->profile_path->store('profiles', 'public');
        }

        try {
            $this->userService->updateUserInformation($this->user, $data);

            session()->flash('success', 'Użytkownik został pomyślnie zaktualizowany!');

            return redirect()->route('users.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Wystąpił błąd podczas aktualizacji użytkownika. Proszę spróbować ponownie.');
        }
    }

    public function render()
    {
        $this->authorize('update', $this->user);
        return view('livewire.users.update-page', [
            'roleOptions' => UserRoles::options()
        ]);
    }
}