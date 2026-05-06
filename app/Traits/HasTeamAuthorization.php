<?php

namespace App\Traits;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

trait HasTeamAuthorization
{
    /**
     * Verifica permisos generales (viewAny, create) que no dependen de un modelo específico.
     * Sintetiza: CanViewAnyHelper, CanCreateHelper.
     */
    public function canPerformTeamAction(string $permission): bool
    {
        $team = Filament::getTenant();

        return $team
            && $this->hasTeamPermission($permission)
            && $this->teams()->where('teams.id', $team->id)->exists();
    }

    /**
     * Verifica permisos sobre un modelo específico (view, update, delete).
     * Sintetiza: CanViewHelper, CanUpdateHelper, CanDeleteHelper, etc.
     * 
     * @param string $permission Nombre del permiso.
     * @param Model $model El registro a evaluar.
     * @param string|null $requiredStatus Estado opcional (ej: 'pending' para delete).
     * @param int|null $timeLimitHours Límite de tiempo opcional para acciones como cancelar.
     */
    public function canPerformModelAction(
        string $permission, 
        Model $model, 
        ?string $requiredStatus = null,
        ?int $timeLimitHours = null
    ): bool {
        $team = Filament::getTenant();

        if (!$team || $model->team_id !== $team->id) {
            return false;
        }

        // Validación de estado (si aplica)
        if ($requiredStatus && $model->getAttribute('status') !== $requiredStatus) {
            return false;
        }

        // Validación de tiempo (si aplica, ej: CanCancelHelper)
        if ($timeLimitHours !== null) {
            $updatedAt = $model->getAttribute('updated_at');
            if (!$updatedAt || $updatedAt->diffInHours(now()) > $timeLimitHours) {
                return false;
            }
        }

        return $this->canPerformTeamAction($permission);
    }

    /**
     * Alias semántico para acciones de eliminación/restauración que requieren estado 'pending'.
     */
    public function canManagePendingModel(string $permission, Model $model): bool
    {
        return $this->canPerformModelAction($permission, $model, 'pending');
    }
}
