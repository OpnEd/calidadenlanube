<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\CourseCreated;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogCourseCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CourseCreated $event): void
    {
        AuditService::logCreate(
            $event->course->team_id,
            'Course',
            $event->course->id,
            description: "Curso '{$event->course->title}' creado",
        );
    }
}
