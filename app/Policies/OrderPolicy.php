<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->hasPermission('nav_orders');
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isActive() && $user->hasPermission('nav_orders');
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        return $user->isActive() && $user->hasPermission('action_create_order');
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isActive() && $user->hasPermission('action_edit_order');
    }

    /**
     * Determine whether the user can delete (soft delete) the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isActive() && $user->hasPermission('action_delete_order');
    }

    /**
     * Determine whether the user can permanently delete the order (Admin only).
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the order.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
