<?php

namespace App\Observers\Quality\Training;

use App\Events\Quality\Training\LessonCreated;
use App\Events\Quality\Training\LessonDeleted;
use App\Events\Quality\Training\LessonUpdated;
use App\Models\Quality\Training\Lesson;
use App\Observers\Quality\Training\Concerns\TracksTrainingModelState;

class LessonObserver
{
    use TracksTrainingModelState;

    public function created(Lesson $lesson): void
    {
        LessonCreated::dispatch($lesson);
    }

    public function updating(Lesson $lesson): void
    {
        if ($this->pendingChanges($lesson) === []) {
            return;
        }

        $this->rememberOriginalState($lesson);
    }

    public function updated(Lesson $lesson): void
    {
        $newValues = $this->meaningfulChanges($lesson);

        if ($newValues === []) {
            return;
        }

        $oldSnapshot = $this->pullOriginalState($lesson);
        $oldValues = array_intersect_key($oldSnapshot, $newValues);

        LessonUpdated::dispatch($lesson, $oldValues, $newValues);
    }

    public function deleting(Lesson $lesson): void
    {
        $lesson->loadMissing('module.course:id,team_id');

        $this->rememberDeletedState($lesson, [
            'team_id' => $lesson->module?->course?->team_id,
            'title' => $lesson->title,
        ]);
    }

    public function deleted(Lesson $lesson): void
    {
        LessonDeleted::dispatch(
            $this->pullDeletedState($lesson),
            (int) $lesson->getKey()
        );
    }
}
