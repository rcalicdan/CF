<?php

namespace App\Observers;

use App\Enums\UserRoles;
use App\Models\User;

class DriverObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if ($user->role === UserRoles::DRIVER->value) {
            $user->driver()->create();
        }
    }
}