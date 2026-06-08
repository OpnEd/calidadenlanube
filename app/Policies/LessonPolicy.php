<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Quality\Training\Lesson;
use App\Models\Quality\Training\Module;
use App\Models\User;
use App\Traits\Filament\Training\HandlesTrainingAuthorization;

class LessonPolicy
{
    use HandlesTrainingAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, PermissionType::VIEW_LESSONS);
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return $this->hasPermission($user, PermissionType::VIEW_LESSONS)
            && $this->canAccessModel($lesson);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, PermissionType::CREATE_LESSONS);
    }

    public function createForModule(User $user, Module $module): bool
    {
        return $this->canCreateInModule($user, $module, PermissionType::CREATE_LESSONS);
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $this->hasPermission($user, PermissionType::EDIT_LESSONS)
            && $this->canMutateModel($lesson);
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $this->hasPermission($user, PermissionType::DELETE_LESSONS)
            && $this->canMutateModel($lesson);
    }

    public function restore(User $user, Lesson $lesson): bool
    {
        return false;
    }

    public function forceDelete(User $user, Lesson $lesson): bool
    {
        return false;
    }
}
