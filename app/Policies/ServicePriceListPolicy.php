<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServicePriceList;
use App\Enums\UserRoles;

class ServicePriceListPolicy
{
    /**
     * Determine whether the user can view any service price lists.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    /**
     * Determine whether the user can view the service price list.
     */
    public function view(User $user, ServicePriceList $servicePriceList): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    /**
     * Determine whether the user can create service price lists.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    /**
     * Determine whether the user can update the service price list.
     */
    public function update(User $user, ServicePriceList $servicePriceList): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    /**
     * Determine whether the user can delete the service price list.
     */
    public function delete(User $user, ServicePriceList $servicePriceList): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the service price list.
     */
    public function restore(User $user, ServicePriceList $servicePriceList): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the service price list.
     */
    public function forceDelete(User $user, ServicePriceList $servicePriceList): bool
    {
        return $user->isAdmin();
    }
}