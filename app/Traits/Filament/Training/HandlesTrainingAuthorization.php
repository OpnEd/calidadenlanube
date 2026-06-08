<?php

namespace App\Traits\Filament\Training;

use App\Enums\PermissionType;
use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Module;
use App\Models\User;
use App\Services\Quality\Training\TenantResolver;
use Filament\Facades\Filament;

trait HandlesTrainingAuthorization
{
    protected function tenantId(): ?int
    {
        return Filament::getTenant()?->id;
    }

    protected function canAccessModel($model): bool
    {
        $modelTeamId = TenantResolver::resolveTeamId($model);
        $tenantId = $this->tenantId();

        return $tenantId !== null
            && ($modelTeamId === $tenantId || $modelTeamId === null);
    }

    protected function canMutateModel($model): bool
    {
        $modelTeamId = TenantResolver::resolveTeamId($model);

        return $this->tenantId() !== null
            && $modelTeamId === $this->tenantId();
    }

    protected function userBelongsToCurrentTenant(User $user): bool
    {
        $tenantId = $this->tenantId();

        return $tenantId !== null
            && $user->teams()->whereKey($tenantId)->exists();
    }

    protected function hasPermission(User $user, PermissionType $permission): bool
    {
        return $this->userBelongsToCurrentTenant($user)
            && $user->hasTeamPermission($permission->value);
    }

    protected function canCreateInCourse(User $user, Course $course, PermissionType $permission): bool
    {
        return $this->hasPermission($user, $permission)
            && $this->canMutateModel($course);
    }

    protected function canCreateInModule(User $user, Module $module, PermissionType $permission): bool
    {
        return $this->hasPermission($user, $permission)
            && $this->canMutateModel($module);
    }
}
