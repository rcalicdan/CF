<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PriceList;
use App\Enums\UserRoles;

class PriceListPolicy
{
    /**
     * Determine whether the user can view any price lists.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    /**
     * Determine whether the user can view the price list.
     */
    public function view(User $user, PriceList $priceList): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    /**
     * Determine whether the user can create price lists.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    /**
     * Determine whether the user can update the price list.
     */
    public function update(User $user, PriceList $priceList): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    /**
     * Determine whether the user can delete the price list.
     */
    public function delete(User $user, PriceList $priceList): bool
    {
        return $user->isAdmin();
    }
}