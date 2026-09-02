<?php

namespace App\Policies;

use App\Models\ProductionBatch;
use App\Models\User;

class ProductionBatchPolicy
{
    /**
     * Determine whether the user can view any production batches.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'production']);
    }

    /**
     * Determine whether the user can view a specific production batch.
     */
    public function view(User $user, ProductionBatch $batch): bool
    {
        return in_array($user->role, ['admin', 'manager', 'production']);
    }

    /**
     * Determine whether the user can create production batches.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'production']);
    }

    /**
     * Determine whether the user can update a production batch.
     */
    public function update(User $user, ProductionBatch $batch): bool
    {
        return in_array($user->role, ['admin', 'manager', 'production']);
    }

    /**
     * Determine whether the user can archive a production batch.
     */
    public function archive(User $user, ProductionBatch $batch): bool
    {
        return in_array($user->role, ['admin', 'manager', 'production']);
    }

    /**
     * Determine whether the user can restore an archived production batch.
     */
    public function restore(User $user, ProductionBatch $batch): bool
    {
        return in_array($user->role, ['admin', 'manager', 'production']);
    }

    /**
     * Determine whether the user can permanently delete a production batch.
     */
    public function delete(User $user, ProductionBatch $batch): bool
    {
        return $user->isAdmin();
    }
}
