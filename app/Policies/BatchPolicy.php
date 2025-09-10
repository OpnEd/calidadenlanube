<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BatchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return \App\Helpers\CanViewAnyHelper::canViewAny($user, 'view-batch');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Batch $batch): bool
    {
        return $user->can('view-batch');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-batch');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Batch $batch): bool
    {
        return $user->can('edit-batch');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Batch $batch): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Batch $batch): bool
    {
        return $user->can('restore-batch');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Batch $batch): bool
    {
        return $user->can('force-delete-batch');
    }
}
