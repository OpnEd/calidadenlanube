<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\LessonDeleted;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogLessonDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LessonDeleted $event): void
    {
        AuditService::logDelete(
            team: $event->lessonData['team_id'] ?? null,
            resourceType: 'Lesson',
            resourceId: $event->lessonId,
            description: "Leccion '{$event->lessonData['title']}' eliminada",
        );
    }
}
