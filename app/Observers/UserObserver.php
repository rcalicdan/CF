<?php 

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function deleting(User $user)
    {
        if ($user->isForceDeleting()) {
            $user->driver()->forceDelete();
        } else {
            $user->driver()->delete();
        }
    }

    public function restoring(User $user)
    {
        $user->driver()->withTrashed()->restore();
    }
}