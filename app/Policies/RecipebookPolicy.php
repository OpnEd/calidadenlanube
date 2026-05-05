<?php

namespace App\Policies;

use App\Helpers\CanCreateHelper;
use App\Helpers\CanDeleteHelper;
use App\Helpers\CanForceDeleteHelper;
use App\Helpers\CanRestoreHelper;
use App\Helpers\CanUpdateHelper;
use App\Helpers\CanViewAnyHelper;
use App\Helpers\CanViewHelper;
use App\Models\Recipebook;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\Response;

class RecipebookPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return CanViewAnyHelper::canViewAny($user, 'view-recipebook');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Recipebook $recipebook): bool
    {
        return CanViewHelper::canView($user, $recipebook, 'view-recipebook');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return CanCreateHelper::canCreate($user, 'create-recipebook');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Recipebook $recipebook): bool
    {
        return CanUpdateHelper::canUpdate($user, $recipebook, 'update-recipebook')
            && $recipebook->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Recipebook $recipebook): bool
    {
        return CanDeleteHelper::canDelete($user, $recipebook, 'delete-recipebook')
            && $recipebook->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Recipebook $recipebook): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Recipebook $recipebook): bool
    {
        return false;
    }

    public function annul(User $user, Recipebook $model): bool
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

        return $role->permissions->contains('name', 'annul-recipebook')
            && $model->team_id === $teamId
            && $user->teams()->where('teams.id', $teamId)->exists()
            && $model->status === 'used';
    }
}
