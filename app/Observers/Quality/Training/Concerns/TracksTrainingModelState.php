<?php

namespace App\Observers\Quality\Training\Concerns;

use Illuminate\Database\Eloquent\Model;

trait TracksTrainingModelState
{
    protected array $originalSnapshots = [];

    protected array $deletedSnapshots = [];

    protected function rememberOriginalState(Model $model): void
    {
        $this->originalSnapshots[spl_object_id($model)] = $model->getRawOriginal();
    }

    protected function pullOriginalState(Model $model): array
    {
        $key = spl_object_id($model);
        $snapshot = $this->originalSnapshots[$key] ?? [];

        unset($this->originalSnapshots[$key]);

        return $snapshot;
    }

    protected function rememberDeletedState(Model $model, array $snapshot): void
    {
        $this->deletedSnapshots[spl_object_id($model)] = $snapshot;
    }

    protected function pullDeletedState(Model $model): array
    {
        $key = spl_object_id($model);
        $snapshot = $this->deletedSnapshots[$key] ?? [];

        unset($this->deletedSnapshots[$key]);

        return $snapshot;
    }

    protected function meaningfulChanges(Model $model): array
    {
        $changes = $model->getChanges();

        unset($changes['updated_at']);

        return $changes;
    }

    protected function pendingChanges(Model $model): array
    {
        $changes = $model->getDirty();

        unset($changes['updated_at']);

        return $changes;
    }
}
