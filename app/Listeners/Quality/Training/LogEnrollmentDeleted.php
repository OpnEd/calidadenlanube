<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\EnrollmentDeleted;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogEnrollmentDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EnrollmentDeleted $event): void
    {
        AuditService::logDelete(
            team: $event->enrollmentData['team_id'] ?? null,
            resourceType: 'Enrollment',
            resourceId: $event->enrollmentId,
            description: "Matricula eliminada: {$event->enrollmentData['user_name']}",
        );
    }
}
