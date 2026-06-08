<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\LessonCreated;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogLessonCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LessonCreated $event): void
    {
        $event->lesson->loadMissing('module.course:id,team_id', 'module:id,title,course_id');

        AuditService::logCreate(
            $event->lesson->module?->course?->team_id,
            'Lesson',
            $event->lesson->id,
            description: "Leccion '{$event->lesson->title}' creada en modulo '{$event->lesson->module?->title}'",
        );
    }
}
