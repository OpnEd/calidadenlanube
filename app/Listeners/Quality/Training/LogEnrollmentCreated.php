<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\EnrollmentCreated;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogEnrollmentCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EnrollmentCreated $event): void
    {
        $event->enrollment->loadMissing('user:id,name', 'course:id,title');

        AuditService::logCreate(
            $event->enrollment->team_id,
            'Enrollment',
            $event->enrollment->id,
            description: "Matricula creada: {$event->enrollment->user?->name} inscrito en '{$event->enrollment->course?->title}'",
        );
    }
}
