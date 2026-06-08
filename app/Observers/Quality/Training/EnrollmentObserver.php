<?php

namespace App\Observers\Quality\Training;

use App\Events\Quality\Training\EnrollmentCreated;
use App\Events\Quality\Training\EnrollmentDeleted;
use App\Events\Quality\Training\EnrollmentUpdated;
use App\Models\Quality\Training\Enrollment;
use App\Observers\Quality\Training\Concerns\TracksTrainingModelState;

class EnrollmentObserver
{
    use TracksTrainingModelState;

    public function created(Enrollment $enrollment): void
    {
        EnrollmentCreated::dispatch($enrollment);
    }

    public function updating(Enrollment $enrollment): void
    {
        if ($this->pendingChanges($enrollment) === []) {
            return;
        }

        $this->rememberOriginalState($enrollment);
    }

    public function updated(Enrollment $enrollment): void
    {
        $newValues = $this->meaningfulChanges($enrollment);

        if ($newValues === []) {
            return;
        }

        $oldSnapshot = $this->pullOriginalState($enrollment);
        $oldValues = array_intersect_key($oldSnapshot, $newValues);

        EnrollmentUpdated::dispatch($enrollment, $oldValues, $newValues);
    }

    public function deleting(Enrollment $enrollment): void
    {
        $enrollment->loadMissing('user:id,name');

        $this->rememberDeletedState($enrollment, [
            'team_id' => $enrollment->team_id,
            'user_name' => $enrollment->user?->name,
        ]);
    }

    public function deleted(Enrollment $enrollment): void
    {
        EnrollmentDeleted::dispatch(
            $this->pullDeletedState($enrollment),
            (int) $enrollment->getKey()
        );
    }
}
