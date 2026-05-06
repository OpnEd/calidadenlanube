<?php

namespace App\Traits;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;

trait HasTeamRoles
{
    /**
     * Obtiene el rol del usuario asociado al equipo (tenant) actual.
     *
     * Este método recupera el rol asignado al usuario dentro del contexto del equipo actual,
     * determinado por el tenant resuelto a través de Filament. Si no se puede resolver un equipo,
     * se registra una advertencia en el log y se retorna null.
     *
     * El método busca el rol considerando tanto roles globales (sin team_id) como roles específicos
     * del equipo actual (con team_id igual al del equipo). Retorna el primer rol encontrado que cumpla
     * con estas condiciones.
     *
     * @return \App\Models\Role|null El rol del usuario para el equipo actual, o null si no existe o no se pudo resolver el equipo.
     */
    /**
     * Get the user's role for the current team (tenant).
     *
     * @return \Spatie\Permission\Models\Role|null
     */
    public function getCurrentTeamRole(): ?\App\Models\Role
    {
        $team = Filament::getTenant();

        if (! $team) {
            Log::warning("No tenant resolved for team-scoped role access.");
            return null;
        }

        $teamId = $team->id;

        return $this->roles()
            ->where(function ($query) use ($teamId) {
                $query->where('model_has_roles.team_id', $teamId)
                      ->orWhereNull('model_has_roles.team_id');
            })
            ->orderByRaw('model_has_roles.team_id IS NULL DESC') // Prioriza el rol global si existe
            ->first();

        /* return $this->roles()
            ->where('model_has_roles.team_id', $teamId)
            ->where(function ($query) use ($teamId) {
                $query->whereNull('roles.team_id')
                      ->orWhere('roles.team_id', $teamId);
            })
            ->first(); */
    }

    /**
     * Verifica si el rol del usuario para el equipo actual posee el permiso especificado.
     *
     * Este método obtiene el rol actual del usuario dentro del equipo activo y comprueba
     * si dicho rol incluye el permiso cuyo nombre se pasa como argumento. Retorna 'true' si
     * el permiso está presente, de lo contrario retorna 'false'.
     *
     * @param string $permissionName Nombre del permiso a verificar.
     * @return bool 'true' si el rol del usuario tiene el permiso, 'false' en caso contrario.
     */
    public function hasTeamPermission(string $permissionName): bool
    {
        $role = $this->getCurrentTeamRole();

        return $role && $role->permissions->contains('name', $permissionName);
    }
}
