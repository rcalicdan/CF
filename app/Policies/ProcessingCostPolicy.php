<?php

namespace App\Policies;

use App\Models\ProcessingCost;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProcessingCostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return !$user->isDriver();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProcessingCost $processingCost): bool
    {
        return !$user->isDriver();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return !$user->isDriver();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProcessingCost $processingCost): bool
    {
        return !$user->isDriver();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProcessingCost $processingCost): bool
    {
        return !$user->isDriver();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProcessingCost $processingCost): bool
    {
        return !$user->isDriver();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProcessingCost $processingCost): bool
    {
        return !$user->isDriver();
    }
}