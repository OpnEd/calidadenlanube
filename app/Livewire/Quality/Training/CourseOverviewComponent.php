<?php

namespace App\Livewire\Quality\Training;

use App\Models\Quality\Training\AssessmentAttempt;
use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use App\Models\Quality\Training\EnrollmentLesson;
use App\Models\Quality\Training\Lesson;
use Livewire\Component;

class CourseOverviewComponent extends Component
{
    public Course $course;
    public Enrollment $record;
    public $team;
    public array $lessonStatuses = [];

    public function mount(Enrollment $record): void
    {
        $this->record = $record->load([
            'course.modules.lessons.assessment:id,lesson_id,title',
        ]);
        $this->course = $this->record->course;
        $this->team = filament()->getTenant();
        $this->loadLessonStatuses();
    }

    protected function loadLessonStatuses(): void
    {
        $lessons = $this->course->modules->pluck('lessons')->flatten();
        $lessonIds = $lessons->pluck('id');

        if ($lessonIds->isEmpty()) {
            return;
        }

        $progressData = $this->record->enrollmentLessons()
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');

        $latestAttempts = AssessmentAttempt::query()
            ->where('enrollment_id', $this->record->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('status', 'completed')
            ->latest('id')
            ->get()
            ->unique('lesson_id')
            ->keyBy('lesson_id');

        foreach ($lessons as $lesson) {
            $progress = $progressData->get($lesson->id);
            $attempt = $latestAttempts->get($lesson->id);

            $this->lessonStatuses[$lesson->id] = $this->resolveLessonStatus($lesson, $progress, $attempt);
        }
    }

    protected function resolveLessonStatus(
        Lesson $lesson,
        ?EnrollmentLesson $progress,
        ?AssessmentAttempt $attempt
    ): array {
        if (! $progress) {
            return ['text' => 'No cursada', 'color' => 'gray'];
        }

        if ($progress->status === EnrollmentLesson::STATUS_IN_PROGRESS) {
            return ['text' => 'En progreso', 'color' => 'warning'];
        }

        if ($lesson->isConsumptionOnly() && in_array($progress->status, [
            EnrollmentLesson::STATUS_CONSUMED,
            EnrollmentLesson::STATUS_PASSED,
        ], true)) {
            return ['text' => 'Vista', 'color' => 'success'];
        }

        if ($progress->status === EnrollmentLesson::STATUS_PASSED || $attempt?->passed) {
            return ['text' => 'Aprobada', 'color' => 'success'];
        }

        if ($progress->status === EnrollmentLesson::STATUS_CONSUMED && $attempt && ! $attempt->passed) {
            return ['text' => 'Reprobada', 'color' => 'danger'];
        }

        if ($progress->status === EnrollmentLesson::STATUS_CONSUMED) {
            return ['text' => 'Pendiente evaluacion', 'color' => 'info'];
        }

        return ['text' => 'No cursada', 'color' => 'gray'];
    }

    public function render()
    {
        return view('livewire.quality.training.course-overview-component');
    }
}
