<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Module;
use App\Models\User;
use App\Traits\Filament\Training\HandlesTrainingAuthorization;

class ModulePolicy
{
    use HandlesTrainingAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, PermissionType::VIEW_MODULES);
    }

    public function view(User $user, Module $module): bool
    {
        return $this->hasPermission($user, PermissionType::VIEW_MODULES)
            && $this->canAccessModel($module);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, PermissionType::CREATE_MODULES);
    }

    public function createForCourse(User $user, Course $course): bool
    {
        return $this->canCreateInCourse($user, $course, PermissionType::CREATE_MODULES);
    }

    public function update(User $user, Module $module): bool
    {
        return $this->hasPermission($user, PermissionType::EDIT_MODULES)
            && $this->canMutateModel($module);
    }

    public function delete(User $user, Module $module): bool
    {
        return $this->hasPermission($user, PermissionType::DELETE_MODULES)
            && $this->canMutateModel($module);
    }

    public function restore(User $user, Module $module): bool
    {
        return false;
    }

    public function forceDelete(User $user, Module $module): bool
    {
        return false;
    }
}
