<?php

namespace App\Policies;

use App\Helpers\CanCreateHelper;
use App\Helpers\CanDeleteHelper;
use App\Helpers\CanForceDeleteHelper;
use App\Helpers\CanRestoreHelper;
use App\Helpers\CanUpdateHelper;
use App\Helpers\CanViewAnyHelper;
use App\Helpers\CanViewHelper;
use App\Models\ProductReception;
use App\Models\User;

class ProductReceptionPolicy
{
    public function viewAny(User $user): bool
    {
        return CanViewAnyHelper::canViewAny($user, 'view-product-reception');
    }

    public function view(User $user, ProductReception $model): bool
    {
        return CanViewHelper::canView($user, $model, 'view-product-reception');
    }

    public function create(User $user): bool
    {
        return CanCreateHelper::canCreate($user, 'create-product-reception');
    }

    public function update(User $user, ProductReception $model): bool
    {
        return CanUpdateHelper::canUpdate($user, $model, 'edit-product-reception')
            && ! $model->isDone();
    }

    public function delete(User $user, ProductReception $model): bool
    {
        return CanDeleteHelper::canDelete($user, $model, 'delete-product-reception');
    }

    public function restore(User $user, ProductReception $model): bool
    {
        return CanRestoreHelper::canRestore($user, $model, 'restore-product-reception');
    }

    public function forceDelete(User $user, ProductReception $model): bool
    {
        return CanForceDeleteHelper::canForceDelete($user, $model, 'force-delete-product-reception');
    }
}
