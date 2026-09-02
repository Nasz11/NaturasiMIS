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
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can view a specific order.
     */
    public function view(User $user, Order $order): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can update an order.
     */
    public function update(User $user, Order $order): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory'])
            && $order->status === 'Pending';
    }

    /**
     * Determine whether the user can update the status of an order.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can archive an order.
     */
    public function archive(User $user, Order $order): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory'])
            && in_array($order->status, ['Completed', 'Cancelled'])
            && !$order->is_archived;
    }

    /**
     * Determine whether the user can delete an order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
