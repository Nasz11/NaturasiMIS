<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    /**
     * Determine whether the user can view any inventory items.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can view a specific inventory item.
     */
    public function view(User $user, InventoryItem $item): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can create an inventory item.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can update an inventory item.
     */
    public function update(User $user, InventoryItem $item): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can record an inventory movement.
     */
    public function recordMovement(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can archive an inventory item.
     */
    public function archive(User $user, InventoryItem $item): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can restore an archived inventory item.
     */
    public function restore(User $user, InventoryItem $item): bool
    {
        return in_array($user->role, ['admin', 'manager', 'inventory']);
    }

    /**
     * Determine whether the user can permanently delete an inventory item.
     */
    public function delete(User $user, InventoryItem $item): bool
    {
        return $user->isAdmin();
    }
}
