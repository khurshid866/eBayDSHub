<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    protected function isCompanyAuthorized(User $user, Order $order): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return !empty($user->company_id) && (int)$order->company_id === (int)$user->company_id;
    }

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
        return $user->isActive() && $user->hasPermission('nav_orders') && $this->isCompanyAuthorized($user, $order);
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
        return $user->isActive() && $user->hasPermission('action_edit_order') && $this->isCompanyAuthorized($user, $order);
    }

    /**
     * Determine whether the user can delete (soft delete) the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isActive() && $user->hasPermission('action_delete_order') && $this->isCompanyAuthorized($user, $order);
    }

    /**
     * Determine whether the user can permanently delete the order (Admin only).
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isAdmin() && $this->isCompanyAuthorized($user, $order);
    }

    /**
     * Determine whether the user can restore the order.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->isAdmin() && $this->isCompanyAuthorized($user, $order);
    }
}
