<?php

namespace App\Policies;

use App\Helpers\CanCreateHelper;
use App\Helpers\CanViewAnyHelper;
use App\Helpers\CanViewHelper;
use App\Models\Document;
use App\Models\User;
use Filament\Facades\Filament;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return CanViewAnyHelper::canViewAny($user, 'view-document');
    }

    public function view(User $user, Document $document): bool
    {
        return CanViewHelper::canView($user, $document, 'view-document');
    }

    public function create(User $user): bool
    {
        return CanCreateHelper::canCreate($user, 'create-document');
    }

    public function update(User $user, Document $document): bool
    {
        return $this->hasTeamPermission($user, 'edit-document', $document)
            && $document->status !== 'published';
    }

    public function delete(User $user, Document $document): bool
    {
        return $this->hasTeamPermission($user, 'delete-document', $document)
            && $document->status !== 'published';
    }

    public function restore(User $user, Document $document): bool
    {
        return $this->hasTeamPermission($user, 'restore-document', $document)
            && $document->status !== 'published';
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return $this->hasTeamPermission($user, 'force-delete-document', $document)
            && $document->status !== 'published';
    }

    private function hasTeamPermission(User $user, string $permission, Document $document): bool
    {
        $team = Filament::getTenant();

        return $team
            && $document->team_id === $team->id
            && $user->hasTeamPermission($permission)
            && $user->teams()->where('teams.id', $team->id)->exists();
    }
}

