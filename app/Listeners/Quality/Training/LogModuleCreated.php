<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\ModuleCreated;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogModuleCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleCreated $event): void
    {
        $event->module->loadMissing('course:id,title,team_id');

        AuditService::logCreate(
            $event->module->course?->team_id,
            'Module',
            $event->module->id,
            description: "Modulo '{$event->module->title}' creado en curso '{$event->module->course?->title}'",
        );
    }
}
