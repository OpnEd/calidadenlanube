<?php

namespace App\Policies;

use App\Enums\PermissionType;
use App\Models\Quality\Training\Course;
use App\Models\User;
use App\Traits\Filament\Training\HandlesTrainingAuthorization;

class CoursePolicy
{
    use HandlesTrainingAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, PermissionType::VIEW_COURSE);
    }

    public function view(User $user, Course $course): bool
    {
        return $this->hasPermission($user, PermissionType::VIEW_COURSE)
            && $this->canAccessModel($course);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, PermissionType::CREATE_COURSE);
    }

    public function update(User $user, Course $course): bool
    {
        return $this->hasPermission($user, PermissionType::EDIT_COURSE)
            && $this->canMutateModel($course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->hasPermission($user, PermissionType::DELETE_COURSE)
            && $this->canMutateModel($course);
    }

    public function restore(User $user, Course $course): bool
    {
        return false;
    }

    public function forceDelete(User $user, Course $course): bool
    {
        return false;
    }
}
