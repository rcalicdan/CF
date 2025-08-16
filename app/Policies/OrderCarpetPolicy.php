<?php

namespace App\Policies;

use App\Enums\UserRoles;
use App\Models\OrderCarpet;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderCarpetPolicy
{
    /**
     * Perform pre-authorization checks
     */
    public function before(User $user): ?bool
    {
        if ($user->role === UserRoles::ADMIN->value) {
            return true; 
        }

        return null; 
    }

    /**
     * Determine whether the user can view any models.
     */
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response|bool
    {
        if ($user->role === UserRoles::EMPLOYEE->value || $user->role === UserRoles::ADMIN->value) {
            return Response::allow();
        }

        if ($user->role === UserRoles::DRIVER->value) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view orders.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrderCarpet $orderCarpet): bool|Response
    {
        if ($user->role === UserRoles::EMPLOYEE->value) {
            return Response::allow();
        }

        if ($user->role === UserRoles::DRIVER->value) {
            return $user->driver->id === $orderCarpet->order->assigned_driver_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $user->isEmployee();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrderCarpet $orderCarpet): bool|Response
    {
        return $user->isEmployee();
    }

    public function delete(User $user, OrderCarpet $orderCarpet): bool|Response
    {
        return $orderCarpet->order->user_id === $user->id;
    }
}
