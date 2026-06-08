<?php

namespace App\Policies;

use App\Models\Quality\Training\Enrollment;
use App\Models\User;
use Filament\Facades\Filament;

class EnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return Filament::getTenant() !== null;
    }

    public function view(User $user, Enrollment $enrollment): bool
    {
        $tenantId = Filament::getTenant()?->id;

        if ((int) $enrollment->team_id !== (int) $tenantId) {
            return false;
        }

        if ($user->isAdmin() || $user->isInstructor()) {
            return true;
        }

        return $user->id === $enrollment->user_id;
    }

    public function create(User $user): bool
    {
        $tenantId = Filament::getTenant()?->id;

        return $tenantId !== null
            && ($user->isAdmin() || $user->isInstructor())
            && $user->teams()->whereKey($tenantId)->exists();
    }

    public function update(User $user, Enrollment $enrollment): bool
    {
        return (int) $enrollment->team_id === (int) Filament::getTenant()?->id
            && ($user->isAdmin() || ($user->isInstructor() && $user->id === $enrollment->course?->instructor_id));
    }

    public function delete(User $user, Enrollment $enrollment): bool
    {
        return false;
    }

    public function restore(User $user, Enrollment $enrollment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Enrollment $enrollment): bool
    {
        return false;
    }
}
