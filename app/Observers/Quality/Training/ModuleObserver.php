<?php

namespace App\Observers\Quality\Training;

use App\Events\Quality\Training\ModuleCreated;
use App\Events\Quality\Training\ModuleDeleted;
use App\Events\Quality\Training\ModuleUpdated;
use App\Models\Quality\Training\Module;
use App\Observers\Quality\Training\Concerns\TracksTrainingModelState;

class ModuleObserver
{
    use TracksTrainingModelState;

    public function created(Module $module): void
    {
        ModuleCreated::dispatch($module);
    }

    public function updating(Module $module): void
    {
        if ($this->pendingChanges($module) === []) {
            return;
        }

        $this->rememberOriginalState($module);
    }

    public function updated(Module $module): void
    {
        $newValues = $this->meaningfulChanges($module);

        if ($newValues === []) {
            return;
        }

        $oldSnapshot = $this->pullOriginalState($module);
        $oldValues = array_intersect_key($oldSnapshot, $newValues);

        ModuleUpdated::dispatch($module, $oldValues, $newValues);
    }

    public function deleting(Module $module): void
    {
        $module->loadMissing('course:id,team_id');

        $this->rememberDeletedState($module, [
            'team_id' => $module->course?->team_id,
            'title' => $module->title,
        ]);
    }

    public function deleted(Module $module): void
    {
        ModuleDeleted::dispatch(
            $this->pullDeletedState($module),
            (int) $module->getKey()
        );
    }
}
