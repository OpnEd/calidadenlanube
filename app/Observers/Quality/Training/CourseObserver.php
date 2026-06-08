<?php

namespace App\Observers\Quality\Training;

use App\Events\Quality\Training\CourseCreated;
use App\Events\Quality\Training\CourseDeleted;
use App\Events\Quality\Training\CourseUpdated;
use App\Models\Quality\Training\Course;
use App\Observers\Quality\Training\Concerns\TracksTrainingModelState;

class CourseObserver
{
    use TracksTrainingModelState;

    public function created(Course $course): void
    {
        CourseCreated::dispatch($course);
    }

    public function updating(Course $course): void
    {
        if ($this->pendingChanges($course) === []) {
            return;
        }

        $this->rememberOriginalState($course);
    }

    public function updated(Course $course): void
    {
        $newValues = $this->meaningfulChanges($course);

        if ($newValues === []) {
            return;
        }

        $oldSnapshot = $this->pullOriginalState($course);
        $oldValues = array_intersect_key($oldSnapshot, $newValues);

        CourseUpdated::dispatch($course, $oldValues, $newValues);
    }

    public function deleting(Course $course): void
    {
        $this->rememberDeletedState($course, [
            'team_id' => $course->team_id,
            'title' => $course->title,
        ]);
    }

    public function deleted(Course $course): void
    {
        CourseDeleted::dispatch(
            $this->pullDeletedState($course),
            (int) $course->getKey()
        );
    }
}
