<?php

namespace App\Policies;

use App\Helpers\CanCreateHelper;
use App\Helpers\CanDeleteHelper;
use App\Helpers\CanForceDeleteHelper;
use App\Helpers\CanRestoreHelper;
use App\Helpers\CanUpdateHelper;
use App\Helpers\CanViewAnyHelper;
use App\Helpers\CanViewHelper;
use App\Models\AnesthesiaSheet;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;

class AnesthesiaSheetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        /* Log::info('Debug AnesthesiaSheetPolicy create:', [
            'user_id' => $user->id,
            'is_surgeon' => $user->is_surgeon,
            'helper_result' => CanCreateHelper::canCreate($user, 'create-anesthesia-sheet'),
        ]); */

        return CanViewAnyHelper::canViewAny($user, 'view-anesthesia-sheet') && $user->is_surgeon;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AnesthesiaSheet $anesthesiaSheet): bool
    {
        return CanViewHelper::canView($user, $anesthesiaSheet, 'view-anesthesia-sheet') && $user->is_surgeon;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return CanCreateHelper::canCreate($user, 'create-anesthesia-sheet') && $user->is_surgeon;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AnesthesiaSheet $anesthesiaSheet): bool
    {
        return CanUpdateHelper::canUpdate($user, $anesthesiaSheet, 'edit-anesthesia-sheet')
            && $user->is_surgeon
            && $anesthesiaSheet->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AnesthesiaSheet $anesthesiaSheet): bool
    {
        return CanDeleteHelper::canDelete($user, $anesthesiaSheet, 'delete-anesthesia-sheet')
            && $user->is_surgeon
            && $anesthesiaSheet->user_id === $user->id
            && $anesthesiaSheet->status === 'opened';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AnesthesiaSheet $anesthesiaSheet): bool
    {
        return CanRestoreHelper::canRestore($user, $anesthesiaSheet, 'restore-anesthesia-sheet')
            && $user->is_surgeon
            && $anesthesiaSheet->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AnesthesiaSheet $anesthesiaSheet): bool
    {
        return CanForceDeleteHelper::canForceDelete($user, $anesthesiaSheet, 'force-delete-anesthesia-sheet')
            && $user->is_surgeon
            && $anesthesiaSheet->user_id === $user->id;
    }

    public function close(User $user, AnesthesiaSheet $anesthesiaSheet): bool
    {
        $team = Filament::getTenant();

        if (!$team) {
            return false;
        }

        $teamId = $team->id;

        if (!$user->teams()->where('teams.id', $teamId)->exists()) {
            return false;
        }

        return $user->is_surgeon
            && $anesthesiaSheet->user_id === $user->id
            && $anesthesiaSheet->status === 'opened'
            && $anesthesiaSheet->team_id === $teamId;
    }

    public function cancel(User $user, AnesthesiaSheet $anesthesiaSheet): bool
    {
        $team = Filament::getTenant();

        if (!$team) {
            return false;
        }

        $teamId = $team->id;

        if (!$user->teams()->where('teams.id', $teamId)->exists()) {
            return false;
        }

        return $user->is_surgeon
            && $anesthesiaSheet->user_id === $user->id
            && in_array($anesthesiaSheet->status, ['opened', 'closed'])
            && $anesthesiaSheet->team_id === $teamId;
    } 
}
