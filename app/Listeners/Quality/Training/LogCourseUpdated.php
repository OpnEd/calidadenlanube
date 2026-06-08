<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\CourseUpdated;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogCourseUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CourseUpdated $event): void
    {
        if ($event->newValues === []) {
            return;
        }

        AuditService::logUpdate(
            $event->course->team_id,
            'Course',
            $event->course->id,
            $event->oldValues,
            $event->newValues,
            description: "Curso '{$event->course->title}' actualizado",
        );
    }
}
