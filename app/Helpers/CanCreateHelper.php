<?php

namespace App\Helpers;

use App\Models\Team;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;

class CanCreateHelper
{

    public static function canCreate($user, $permission): bool
    {
        $team = Filament::getTenant();

        /* Log::info('CanCreateHelper Debug:', [
            'user_id' => $user->id,
            'permission' => $permission,
            'team_id' => $team?->id,
            'has_permission_method' => $user->hasTeamPermission($permission),
            'user_belongs_to_team' => $team ? $user->teams()->where('teams.id', $team->id)->exists() : false,
        ]); */

        return $team
            && $user->hasTeamPermission($permission)
            && $user->teams()->where('teams.id', $team->id)->exists();
    }
}
