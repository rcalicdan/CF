<?php

namespace App\ActionService;

use App\Models\User;

class UserService
{
    public function getAllUsers()
    {
        $users = User::with('driver')
            ->when(request('first_name'), function ($query) {
                $query->where('first_name', 'like', '%'.request('first_name').'%');
            })
            ->when(request('last_name'), function ($query) {
                $query->where('last_name', 'like', '%'.request('last_name').'%');
            })
            ->when(request('email'), function ($query) {
                $query->where('email', 'like', '%'.request('email').'%');
            })
            ->when(request('role'), function ($query) {
                $query->where('role', request('role'));
            })
            ->paginate(30);

        return $users;
    }

    public function getUserInformation(User $user)
    {
        $user::with('driver')->get();

        return $user;
    }

    public function storeNewUser(array $data)
    {
        $user = User::create($data);

        return $user;
    }

    public function updateUserInformation(User $user, array $data)
    {
        $user->update($data);

        return $user;
    }

    public function deleteUserInformation(User $user)
    {
        $user->delete();

        return true;
    }
}
