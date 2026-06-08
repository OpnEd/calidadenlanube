<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\ModuleUpdated;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogModuleUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleUpdated $event): void
    {
        if ($event->newValues === []) {
            return;
        }

        $event->module->loadMissing('course:id,team_id');

        AuditService::logUpdate(
            $event->module->course?->team_id,
            'Module',
            $event->module->id,
            $event->oldValues,
            $event->newValues,
            description: "Modulo '{$event->module->title}' actualizado",
        );
    }
}
