<?php

namespace App\Policies;

use App\Models\Quality\Training\Assessment;
use App\Models\User;
use Filament\Facades\Filament;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() 
            || (Filament::getTenant() !== null && $user->isInstructor());
    }

    public function view(User $user, Assessment $assessment): bool
    {
        return $user->isAdmin() || $assessment->isVisibleToTeam(Filament::getTenant()?->id)
            && ($user->isAdmin() || $user->isInstructor());
    }

    public function create(User $user): bool
    {
        $tenantId = Filament::getTenant()?->id;

        return $tenantId !== null
            && ($user->isAdmin() || $user->isInstructor())
            && $user->teams()->whereKey($tenantId)->exists();
    }

    public function update(User $user, Assessment $assessment): bool
    {
        return $user->isAdmin() 
            || $assessment->isOwnedByTeam(Filament::getTenant()?->id)
            && ($user->isAdmin() || ($user->isInstructor() && $user->id === $assessment->course?->instructor_id));
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        return $this->update($user, $assessment);
    }

    public function restore(User $user, Assessment $assessment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Assessment $assessment): bool
    {
        return false;
    }
}
