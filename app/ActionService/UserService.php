<?php

namespace App\ActionService;

use App\Models\User;

class UserService
{
    /**
     * Get a paginated list of users.
     * By default, it returns only active users.
     * This can be overridden by passing the 'active' query parameter.
     * e.g., ?active=false (for inactive) or ?active=all (for all users).
     */
    public function getAllUsers()
    {
        $query = User::with('driver')
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
            });

        if (request()->has('active')) {
            if (request('active') !== 'all') {
                $query->where('active', filter_var(request('active'), FILTER_VALIDATE_BOOLEAN));
            }
        } else {
            $query->where('active', true);
        }

        return $query->paginate(30);
    }

    /**
     * Load the 'driver' relationship on the given user instance.
     */
    public function getUserInformation(User $user)
    {
        $user->load('driver');
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
