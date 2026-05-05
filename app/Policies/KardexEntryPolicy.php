<?php

namespace App\Policies;

use App\Helpers\CanCreateHelper;
use App\Helpers\CanViewAnyHelper;
use App\Helpers\CanViewHelper;
use App\Models\KardexEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KardexEntryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return CanViewAnyHelper::canViewAny($user, 'view-kardex');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KardexEntry $kardexEntry): bool
    {
        return CanViewHelper::canView($user, $kardexEntry, 'view-kardex');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return CanCreateHelper::canCreate($user, 'create-kardex');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KardexEntry $kardexEntry): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KardexEntry $kardexEntry): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, KardexEntry $kardexEntry): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, KardexEntry $kardexEntry): bool
    {
        return false;
    }
}
