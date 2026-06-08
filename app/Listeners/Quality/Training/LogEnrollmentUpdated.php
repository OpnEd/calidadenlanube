<?php

namespace App\Listeners\Quality\Training;

use App\Events\Quality\Training\EnrollmentUpdated;
use App\Services\Quality\AuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogEnrollmentUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(EnrollmentUpdated $event): void
    {
        if ($event->newValues === []) {
            return;
        }

        $event->enrollment->loadMissing('user:id,name');

        $description = "Matricula actualizada: {$event->enrollment->user?->name}";

        if (isset($event->newValues['status']) && (($event->oldValues['status'] ?? null) !== $event->newValues['status'])) {
            $oldStatus = $event->oldValues['status'] ?? 'n/a';
            $description .= " - Estado: {$oldStatus} -> {$event->newValues['status']}";
        }

        if (isset($event->newValues['progress'])) {
            $oldProgress = $event->oldValues['progress'] ?? 0;
            $description .= " - Progreso: {$oldProgress}% -> {$event->newValues['progress']}%";
        }

        AuditService::logUpdate(
            $event->enrollment->team_id,
            'Enrollment',
            $event->enrollment->id,
            $event->oldValues,
            $event->newValues,
            description: $description,
        );
    }
}
