<?php

namespace App\Services\Quality;

use App\Models\Quality\Training\AssessmentAttempt;
use App\Models\Quality\Training\Course;
use App\Models\Quality\Training\Enrollment;
use App\Models\Quality\Training\EnrollmentLesson;
use App\Models\Quality\Training\Lesson;

class EnrollmentLessonService
{
    public function initializeForEnrollment(Enrollment $enrollment, ?Course $course = null): void
    {
        $course ??= $enrollment->course()->with('modules.lessons')->first();

        if (! $course) {
            return;
        }

        $lessons = $course->modules
            ->pluck('lessons')
            ->flatten()
            ->unique('id');

        foreach ($lessons as $lesson) {
            EnrollmentLesson::query()->firstOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'status' => EnrollmentLesson::STATUS_NOT_STARTED,
                ],
            );
        }
    }

    public function getOrCreate(Enrollment $enrollment, Lesson $lesson): EnrollmentLesson
    {
        return EnrollmentLesson::query()->firstOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'status' => EnrollmentLesson::STATUS_NOT_STARTED,
            ],
        );
    }

    public function touchAccess(EnrollmentLesson $enrollmentLesson): EnrollmentLesson
    {
        $now = now();

        $enrollmentLesson->last_accessed_at = $now;

        if (! $enrollmentLesson->started_at) {
            $enrollmentLesson->started_at = $now;
        }

        if ($enrollmentLesson->status === EnrollmentLesson::STATUS_NOT_STARTED) {
            $enrollmentLesson->status = EnrollmentLesson::STATUS_IN_PROGRESS;
        }

        $enrollmentLesson->save();
        $this->touchEnrollment($enrollmentLesson->enrollment, $now);

        return $enrollmentLesson->fresh();
    }

    public function markConsumed(EnrollmentLesson $enrollmentLesson): EnrollmentLesson
    {
        $now = now();

        if ($enrollmentLesson->status === EnrollmentLesson::STATUS_PASSED) {
            $this->touchEnrollment($enrollmentLesson->enrollment, $now);
            return $enrollmentLesson->fresh();
        }

        $enrollmentLesson->started_at ??= $now;
        $enrollmentLesson->last_accessed_at = $now;
        $enrollmentLesson->consumed_at ??= $now;
        $enrollmentLesson->completed_at ??= $now;
        $enrollmentLesson->passed = false;
        $enrollmentLesson->passed_at = null;
        $enrollmentLesson->approved_attempt_id = null;
        $enrollmentLesson->status = EnrollmentLesson::STATUS_CONSUMED;
        $enrollmentLesson->save();

        $this->touchEnrollment($enrollmentLesson->enrollment, $now);
        $this->recalculateEnrollmentProgress($enrollmentLesson->enrollment);

        return $enrollmentLesson->fresh();
    }

    public function markPassed(EnrollmentLesson $enrollmentLesson, AssessmentAttempt $attempt): EnrollmentLesson
    {
        $now = now();

        $enrollmentLesson->started_at ??= $now;
        $enrollmentLesson->last_accessed_at = $now;
        $enrollmentLesson->consumed_at ??= $now;
        $enrollmentLesson->completed_at ??= $now;
        $enrollmentLesson->passed = true;
        $enrollmentLesson->passed_at = $now;
        $enrollmentLesson->approved_attempt_id = $attempt->id;
        $enrollmentLesson->status = EnrollmentLesson::STATUS_PASSED;
        $enrollmentLesson->save();

        $this->touchEnrollment($enrollmentLesson->enrollment, $now);
        $this->recalculateEnrollmentProgress($enrollmentLesson->enrollment);

        return $enrollmentLesson->fresh();
    }

    public function recalculateEnrollmentProgress(Enrollment $enrollment): Enrollment
    {
        $enrollment->updateProgress();

        return $enrollment->fresh();
    }

    protected function touchEnrollment(Enrollment $enrollment, $now): void
    {
        $enrollment->started_at ??= $now;
        $enrollment->last_accessed_at = $now;

        if ($enrollment->status === Enrollment::STATUS_NOT_STARTED) {
            $enrollment->status = Enrollment::STATUS_IN_PROGRESS;
        }

        $enrollment->saveQuietly();
    }
}
