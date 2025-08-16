<?php

namespace App\Policies;

use App\Enums\OrderPaymentStatus;
use App\Enums\UserRoles;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
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
    public function viewAny(User $user): Response|bool
    {
        if ($user->role === UserRoles::EMPLOYEE->value) {
            return true;
        }

        if ($user->role === UserRoles::DRIVER->value) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view orders.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): Response|bool
    {
        if ($user->role === UserRoles::EMPLOYEE->value) {
            return Response::allow();
        }

        if ($user->role === UserRoles::DRIVER->value) {
            return $user->driver->id === $order->assigned_driver_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response|bool
    {
        if($user->isDriver()){
            return false;
        }

        return true;
    }

    public function createCarpet(User $user, Order $order)
    {
        return $user->isEmployee() && $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): Response|bool
    {
        return $user->isEmployee() && $order->orderPayment?->status !== OrderPaymentStatus::COMPLETED->value;
    }
}
