<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\ModuleDeleted;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogModuleDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleDeleted $event): void
    {
        AuditService::logDelete(
            team: $event->moduleData['team_id'] ?? null,
            resourceType: 'Module',
            resourceId: $event->moduleId,
            description: "Modulo '{$event->moduleData['title']}' eliminado",
        );
    }
}
