<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\LessonUpdated;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogLessonUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LessonUpdated $event): void
    {
        if ($event->newValues === []) {
            return;
        }

        $event->lesson->loadMissing('module.course:id,team_id');

        AuditService::logUpdate(
            $event->lesson->module?->course?->team_id,
            'Lesson',
            $event->lesson->id,
            $event->oldValues,
            $event->newValues,
            description: "Leccion '{$event->lesson->title}' actualizada",
        );
    }
}
