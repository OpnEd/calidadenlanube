<?php

namespace App\Policies;

use App\Helpers\CanCancelHelper;
use App\Helpers\CanConfirmHelper;
use App\Helpers\CanCreateHelper;
use App\Helpers\CanDeleteHelper;
use App\Helpers\CanForceDeleteHelper;
use App\Helpers\CanRestoreHelper;
use App\Helpers\CanUpdateHelper;
use App\Helpers\CanViewAnyHelper;
use App\Helpers\CanViewHelper;
use App\Models\Sale;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
        //return CanViewAnyHelper::canViewAny($user, 'view-sale');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sale $model): bool
    {
        return true;
        //return CanViewHelper::canView($user, $model, 'view-sale');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
        //return CanCreateHelper::canCreate($user, 'create-sale');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $model): bool
    {
        return true;
        //return CanUpdateHelper::canUpdate($user, $model, 'edit-sale')
         //   && $model->status === 'in-progress';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sale $model): bool
    {
        return true;
        //return CanDeleteHelper::canDelete($user, $model, 'delete-sale');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sale $model): bool
    {
        return CanRestoreHelper::canRestore($user, $model, 'restore-sale');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sale $model): bool
    {
        return CanForceDeleteHelper::canForceDelete($user, $model, 'force-delete-sale');
    }
    
    public function confirm(User $user, Sale $model): bool
    {
        $team = Filament::getTenant();

        if (!$team) {
            return false;
        }
        
        $teamId = $team->id;

        // Obtiene el primer rol del equipo actual (usando team_id explícito)
        $role = $user->roles()
            ->where('model_has_roles.team_id', $teamId)
            ->where(function ($query) use ($teamId) {
                $query->whereNull('roles.team_id')
                    ->orWhere('roles.team_id', $teamId);
            })
            ->first();

        if (!$role) {
            return false; // Usuario no tiene roles en este equipo
        }

        return $role->permissions->contains('name', 'confirm-purchase')
            && $model->team_id === $teamId
            && $model->status === 'in-progress'
            && $user->teams()->where('teams.id', $teamId)->exists();
    }
    
    public function cancel(User $user, Sale $model): bool
    {        
        $team = Filament::getTenant();

        if (!$team) {
            return false;
        }
        
        $teamId = $team->id;

        // Obtiene el primer rol del equipo actual (usando team_id explícito)
        $role = $user->roles()
            ->where('model_has_roles.team_id', $teamId)
            ->where(function ($query) use ($teamId) {
                $query->whereNull('roles.team_id')
                    ->orWhere('roles.team_id', $teamId);
            })
            ->first();

        if (!$role) {
            return false; // Usuario no tiene roles en este equipo
        }

        return $role->permissions->contains('name', 'cancel-sale')
            && $model->team_id === $teamId
            && $user->teams()->where('teams.id', $teamId)->exists()
            && $model->status === 'confirmed'
            && $model->updated_at->diffInHours(now()) <= 2;
    }
}
